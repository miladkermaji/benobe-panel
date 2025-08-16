<?php

namespace App\Livewire\Admin\Panel\Tools;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileManager extends Component
{
    use WithFileUploads;

    public $currentPath   = '';
    public $newFolderName = '';
    public $filesToUpload = [];
    public $search        = '';
    public $renamingPath  = null;
    public $newName       = '';
    public $selectedImage = null;
    public $editingFile   = null;
    public $fileContent   = '';
    public $filter        = 'all'; // all|images|text|folders
    public $ready         = false; // lazy load gate
    public $page          = 1; // pagination
    public $perPage       = 12; // items per page (reduced from 20)
    public $totalItems    = 0; // total items count

    protected $rules = [
        'newFolderName'   => 'required|string|max:255',
        'filesToUpload.*' => 'file|max:51200', // حداکثر 50MB
        'newName'         => 'required|string|max:255',
        'fileContent'     => 'nullable|string',
    ];

    protected $messages = [
        'newFolderName.required'   => 'نام پوشه نمی‌تواند خالی باشد.',
        'newFolderName.string'     => 'نام پوشه باید یک رشته متنی باشد.',
        'newFolderName.max'        => 'نام پوشه نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        'filesToUpload.*.file'     => 'فایل انتخاب‌شده معتبر نیست.',
        'filesToUpload.*.max'      => 'حجم هر فایل نمی‌تواند بیشتر از ۵۰ مگابایت باشد.',
        'newName.required'         => 'نام جدید نمی‌تواند خالی باشد.',
        'newName.string'           => 'نام جدید باید یک رشته متنی باشد.',
        'newName.max'              => 'نام جدید نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
    ];

    // FTP disk configuration
    protected $ftpDisk = 'public';

    public function loadItems(): void
    {
        try {
            // Set a longer timeout for initial load
            set_time_limit(300); // 5 minutes for FTP operations

            // Register shutdown function to handle timeout
            register_shutdown_function(function () {
                $error = error_get_last();
                if ($error && $error['type'] === E_ERROR && strpos($error['message'], 'Maximum execution time') !== false) {
                    $this->dispatch('toast', 'زمان بارگذاری به پایان رسید. لطفاً دوباره تلاش کنید.', ['type' => 'error']);
                }
            });

            // Test FTP connection with timeout
            $this->testFtpConnection();

            // Just mark as ready - actual loading will happen in render() with pagination
            $this->ready = true;
        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در بارگذاری فایل‌ها: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    /**
     * Test FTP connection before proceeding with timeout handling
     */
    protected function testFtpConnection()
    {
        try {
            // Set a reasonable timeout for connection test
            set_time_limit(30);

            $disk = Storage::disk($this->ftpDisk);

            // Try to list root directory to test connection with retry mechanism
            $maxRetries = 3;
            $retryDelay = 2; // seconds

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    // Use a simple operation to test connection
                    $disk->exists('/');
                    break; // Success, exit retry loop
                } catch (\Exception $e) {
                    if ($attempt === $maxRetries) {
                        throw $e; // Last attempt failed
                    }

                    // Wait before retry
                    sleep($retryDelay);
                    $retryDelay *= 2; // Exponential backoff
                }
            }

        } catch (\Exception $e) {
            throw new \Exception('خطا در اتصال به هاست دانلود: ' . $e->getMessage());
        }
    }

    public function updatedFilesToUpload()
    {
        $this->validateOnly('filesToUpload.*');
        $this->uploadFiles();
    }

    public function createFolder()
    {
        $this->validateOnly('newFolderName');
        $path = $this->currentPath ? $this->currentPath . '/' . $this->newFolderName : $this->newFolderName;

        try {
            if (!Storage::disk($this->ftpDisk)->exists($path)) {
                Storage::disk($this->ftpDisk)->makeDirectory($path);

                // Create file record in database
                File::create([
                    'name' => $this->newFolderName,
                    'path' => $path,
                    'type' => 'folder',
                ]);

                $this->newFolderName = '';
                $this->dispatch('toast', 'پوشه با موفقیت در هاست دانلود ایجاد شد.', ['type' => 'success']);
            } else {
                $this->dispatch('toast', 'پوشه‌ای با این نام در هاست دانلود وجود دارد.', ['type' => 'error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در ایجاد پوشه در هاست دانلود: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function uploadFiles()
    {
        $this->validateOnly('filesToUpload');

        try {
            foreach ($this->filesToUpload as $file) {
                $fileName = $file->getClientOriginalName();
                $path     = $this->currentPath ? $this->currentPath . '/' . $fileName : $fileName;

                // Store the uploaded file from Livewire's temporary storage to the FTP-backed disk
                $file->storeAs($this->currentPath, $fileName, $this->ftpDisk);

                // Create file record in database
                File::create([
                    'name'      => $fileName,
                    'path'      => $path,
                    'type'      => 'file',
                    'extension' => $file->getClientOriginalExtension(),
                    'size'      => $file->getSize(),
                ]);
            }

            $this->filesToUpload = [];
            $this->dispatch('toast', 'فایل‌ها با موفقیت در هاست دانلود آپلود شدند.', ['type' => 'success']);

        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در آپلود فایل‌ها به هاست دانلود: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteItem($path)
    {
        try {
            if (Storage::disk($this->ftpDisk)->exists($path)) {
                $fileRecord = File::where('path', $path)->first();

                if ($fileRecord && $fileRecord->type === 'folder') {
                    Storage::disk($this->ftpDisk)->deleteDirectory($path);
                    File::where('path', $path)->orWhere('path', 'like', "$path/%")->delete();
                } else {
                    Storage::disk($this->ftpDisk)->delete($path);
                    File::where('path', $path)->delete();
                }

                $this->dispatch('toast', 'آیتم با موفقیت از هاست دانلود حذف شد.', ['type' => 'success']);
            } else {
                $this->dispatch('toast', 'آیتم در هاست دانلود یافت نشد.', ['type' => 'error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در حذف آیتم از هاست دانلود: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function startRename($path)
    {
        $this->renamingPath = $path;
        $this->newName      = basename($path);
    }

    public function renameItem()
    {
        $this->validateOnly('newName');
        $oldPath = $this->renamingPath;
        $newPath = $this->currentPath ? $this->currentPath . '/' . $this->newName : $this->newName;

        try {
            if (Storage::disk($this->ftpDisk)->exists($oldPath)) {
                if (!Storage::disk($this->ftpDisk)->exists($newPath)) {
                    Storage::disk($this->ftpDisk)->move($oldPath, $newPath);

                    $file = File::where('path', $oldPath)->first();
                    if ($file) {
                        $file->update([
                            'name' => $this->newName,
                            'path' => $newPath,
                        ]);
                    }

                    $this->renamingPath = null;
                    $this->newName      = '';
                    $this->dispatch('toast', 'نام آیتم با موفقیت در هاست دانلود تغییر کرد.', ['type' => 'success']);
                } else {
                    $this->dispatch('toast', 'نام جدید قبلاً در هاست دانلود وجود دارد.', ['type' => 'error']);
                }
            } else {
                $this->dispatch('toast', 'آیتم در هاست دانلود یافت نشد.', ['type' => 'error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در تغییر نام آیتم در هاست دانلود: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function cancelRename()
    {
        $this->renamingPath = null;
        $this->newName      = '';
    }

    public function changePath($path)
    {
        $this->currentPath = $path;
        $this->reset(['search', 'selectedImage', 'renamingPath', 'newName', 'editingFile', 'fileContent', 'filter']);
    }

    public function goBack()
    {
        $segments = explode('/', $this->currentPath);
        array_pop($segments);
        $this->currentPath = implode('/', array_filter($segments));
        $this->reset(['selectedImage', 'renamingPath', 'newName', 'editingFile', 'fileContent', 'filter']);
    }

    public function selectImage($url)
    {
        $this->selectedImage = $url;
    }

    public function closePreview()
    {
        $this->selectedImage = null;
    }

    public function editFile($path)
    {
        try {
            if (Storage::disk($this->ftpDisk)->exists($path)) {
                $this->editingFile = $path;
                $this->fileContent = Storage::disk($this->ftpDisk)->get($path);
            } else {
                $this->dispatch('toast', 'فایل در هاست دانلود یافت نشد.', ['type' => 'error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در خواندن فایل از هاست دانلود: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function saveFile()
    {
        $this->validateOnly('fileContent');
        if ($this->editingFile) {
            try {
                Storage::disk($this->ftpDisk)->put($this->editingFile, $this->fileContent);
                $this->editingFile = null;
                $this->fileContent = '';
                $this->dispatch('toast', 'فایل با موفقیت در هاست دانلود ذخیره شد.', ['type' => 'success']);
            } catch (\Exception $e) {
                $this->dispatch('toast', 'خطا در ذخیره فایل در هاست دانلود: ' . $e->getMessage(), ['type' => 'error']);
            }
        }
    }

    public function closeEditor()
    {
        if ($this->editingFile && $this->fileContent !== Storage::disk($this->ftpDisk)->get($this->editingFile)) {
            $this->dispatch('confirmCloseEditor');
        } else {
            $this->editingFile = null;
            $this->fileContent = '';
        }
    }

    public function confirmClose($save = false)
    {
        if ($save) {
            $this->saveFile();
        } else {
            $this->editingFile = null;
            $this->fileContent = '';
        }
    }

    /**
     * Clear cache for current path to force refresh
     */
    public function clearCache()
    {
        try {
            $cacheKey = "file_manager_files_{$this->currentPath}";
            Cache::forget($cacheKey);

            $cacheKey = "file_manager_folders_{$this->currentPath}";
            Cache::forget($cacheKey);

            $this->dispatch('toast', 'کش پاک شد و فایل‌ها دوباره بارگذاری می‌شوند.', ['type' => 'success']);

        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در پاک کردن کش: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    /**
     * Refresh current directory contents
     */
    public function refreshDirectory()
    {
        try {
            $this->clearCache();
            $this->dispatch('toast', 'فایل‌ها در حال بارگذاری مجدد...', ['type' => 'info']);

        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در بارگذاری مجدد: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function nextPage()
    {
        $this->page++;
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function goToPage($page)
    {
        $this->page = max(1, $page);
    }

    private function getCachedFileList($path)
    {
        $cacheKey = "file_manager_files_{$path}";
        $cacheDuration = 600; // 10 minutes for better performance

        return Cache::remember($cacheKey, $cacheDuration, function () use ($path) {
            try {
                // Set timeout for file listing
                set_time_limit(60);

                $files = Storage::disk($this->ftpDisk)->allFiles($path);

                // Limit the number of files to prevent memory issues
                return array_slice($files, 0, 1000);

            } catch (\Exception $e) {
                // Log the error for debugging
                Log::warning('FTP file listing failed for path: ' . $path . ' - Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    private function getCachedFolderList($path)
    {
        $cacheKey = "file_manager_folders_{$path}";
        $cacheDuration = 600; // 10 minutes for better performance

        return Cache::remember($cacheKey, $cacheDuration, function () use ($path) {
            try {
                // Set timeout for folder listing
                set_time_limit(60);

                $folders = Storage::disk($this->ftpDisk)->allDirectories($path);

                // Limit the number of folders to prevent memory issues
                return array_slice($folders, 0, 500);

            } catch (\Exception $e) {
                // Log the error for debugging
                Log::warning('FTP folder listing failed for path: ' . $path . ' - Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    public function render()
    {
        $baseUrl = rtrim((string) config('filesystems.disks.' . $this->ftpDisk . '.url'), '/');

        if (!$this->ready) {
            return view('livewire.admin.panel.tools.file-manager', [
                'items' => collect(),
                'totalItems' => 0,
                'currentPage' => 1,
                'totalPages' => 1,
            ]);
        }

        try {
            // Set a reasonable timeout for file operations
            set_time_limit(120); // 2 minutes for render operations

            // Get files with error handling and timeout protection
            $files = collect();
            try {
                $allFiles = $this->getCachedFileList($this->currentPath);
                $files = collect($allFiles)
                    ->filter(fn ($file) => !$this->search || stripos(basename($file), $this->search) !== false)
                    ->map(function ($file) use ($baseUrl) {
                        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                        $isText  = in_array($extension, ['txt', 'md', 'log', 'json', 'xml', 'html', 'htm', 'css', 'js', 'php', 'py', 'java', 'c', 'cpp', 'h', 'sql', 'csv', 'yml', 'yaml', 'ini', 'conf', 'config']);

                        $url = $baseUrl !== ''
                            ? $baseUrl . '/' . ltrim($file, '/')
                            : $file; // fallback: raw path

                        return [
                            'name'    => basename($file),
                            'path'    => $file,
                            'type'    => 'file',
                            'url'     => $url,
                            'isImage' => $isImage,
                            'isText'  => $isText,
                        ];
                    });
            } catch (\Exception $e) {
                // If files loading fails, continue with empty collection
                Log::warning('Files processing failed: ' . $e->getMessage());
                $files = collect();
            }

            // Get folders with error handling and timeout protection
            $folders = collect();
            try {
                $allFolders = $this->getCachedFolderList($this->currentPath);
                $folders = collect($allFolders)
                    ->filter(fn ($folder) => !$this->search || stripos(basename($folder), $this->search) !== false)
                    ->map(fn ($folder) => [
                        'name' => basename($folder),
                        'path' => $folder,
                        'type' => 'folder',
                    ]);
            } catch (\Exception $e) {
                // If folders loading fails, continue with empty collection
                Log::warning('Folders processing failed: ' . $e->getMessage());
                $folders = collect();
            }

            // Apply filter
            switch ($this->filter) {
                case 'images':
                    $files = $files->filter(fn ($f) => $f['isImage']);
                    $folders = collect(); // Hide folders when filtering for images
                    break;
                case 'text':
                    $files = $files->filter(fn ($f) => $f['isText']);
                    $folders = collect(); // Hide folders when filtering for text files
                    break;
                case 'folders':
                    $files = collect(); // Hide files when filtering for folders
                    // Keep folders as they are
                    break;
                case 'all':
                default:
                    // Show both files and folders
                    break;
            }

            $allItems = $folders->merge($files)->sortBy('name')->values();
            $this->totalItems = $allItems->count();

            // Apply pagination
            $totalPages = ceil($this->totalItems / $this->perPage);
            $this->page = min($this->page, max(1, $totalPages));

            $items = $allItems->forPage($this->page, $this->perPage);

        } catch (\Exception $e) {
            Log::error('File manager render failed: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در بارگذاری فایل‌ها از هاست دانلود: ' . $e->getMessage(), ['type' => 'error']);
            $items = collect();
            $this->totalItems = 0;
            $totalPages = 1;
        }

        return view('livewire.admin.panel.tools.file-manager', [
            'items' => $items,
            'totalItems' => $this->totalItems,
            'currentPage' => $this->page,
            'totalPages' => $totalPages ?? 1,
        ]);
    }
}
