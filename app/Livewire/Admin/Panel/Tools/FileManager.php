<?php

namespace App\Livewire\Admin\Panel\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Admin\Panel\Tools\File;
use Illuminate\Support\Facades\Storage;

class FileManager extends Component
{
    use WithFileUploads;

    public $currentPath = '';
    public $newFolderName = '';
    public $filesToUpload = [];
    public $search = '';
    public $renamingPath = null;
    public $newName = '';
    public $selectedImage = null;
    public $editingFile = null;
    public $fileContent = '';

    protected $rules = [
        'newFolderName' => 'required|string|max:255',
        'filesToUpload.*' => 'file|max:10240', // حداکثر 10MB
        'newName' => 'required|string|max:255',
        'fileContent' => 'nullable|string',
    ];

    public function updatedFilesToUpload()
    {
        $this->validateOnly('filesToUpload.*');
        $this->uploadFiles();
    }

    public function createFolder()
    {
        $this->validateOnly('newFolderName');
        $path = $this->currentPath ? $this->currentPath . '/' . $this->newFolderName : $this->newFolderName;

        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
            File::create([
                'name' => $this->newFolderName,
                'path' => $path,
                'type' => 'folder',
            ]);
            $this->newFolderName = '';
            $this->dispatch('toast', 'پوشه با موفقیت ایجاد شد.', ['type' => 'success']);
        } else {
            $this->dispatch('toast', 'پوشه‌ای با این نام وجود دارد.', ['type' => 'error']);
        }
    }

    public function uploadFiles()
    {
        $this->validateOnly('filesToUpload');

        foreach ($this->filesToUpload as $file) {
            $fileName = $file->getClientOriginalName();
            $path = $this->currentPath ? $this->currentPath . '/' . $fileName : $fileName;
            Storage::disk('public')->putFileAs($this->currentPath, $file, $fileName);

            File::create([
                'name' => $fileName,
                'path' => $path,
                'type' => 'file',
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
            ]);
        }
        $this->filesToUpload = [];
        $this->dispatch('toast', 'فایل‌ها با موفقیت آپلود شدند.', ['type' => 'success']);
    }

    public function deleteItem($path)
    {
        if (Storage::disk('public')->exists($path)) {
            if (Storage::disk('public')->directoryExists($path)) {
                Storage::disk('public')->deleteDirectory($path);
                File::where('path', $path)->orWhere('path', 'like', "$path/%")->delete();
            } else {
                Storage::disk('public')->delete($path);
                File::where('path', $path)->delete();
            }
            $this->dispatch('toast', 'آیتم با موفقیت حذف شد.', ['type' => 'success']);
        } else {
            $this->dispatch('toast', 'آیتم یافت نشد.', ['type' => 'error']);
        }
    }

    public function startRename($path)
    {
        $this->renamingPath = $path;
        $this->newName = basename($path);
    }

    public function renameItem()
    {
        $this->validateOnly('newName');
        $oldPath = $this->renamingPath;
        $newPath = $this->currentPath ? $this->currentPath . '/' . $this->newName : $this->newName;

        if (Storage::disk('public')->exists($oldPath)) {
            if (!Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->move($oldPath, $newPath);
                $file = File::where('path', $oldPath)->first();
                if ($file) {
                    $file->update([
                        'name' => $this->newName,
                        'path' => $newPath,
                    ]);
                }
                $this->renamingPath = null;
                $this->newName = '';
                $this->dispatch('toast', 'نام آیتم با موفقیت تغییر کرد.', ['type' => 'success']);
            } else {
                $this->dispatch('toast', 'نام جدید قبلاً وجود دارد.', ['type' => 'error']);
            }
        } else {
            $this->dispatch('toast', 'آیتم یافت نشد.', ['type' => 'error']);
        }
    }

    public function cancelRename()
    {
        $this->renamingPath = null;
        $this->newName = '';
    }

    public function changePath($path)
    {
        $this->currentPath = $path;
        $this->reset(['search', 'selectedImage', 'renamingPath', 'newName', 'editingFile', 'fileContent']);
    }

    public function goBack()
    {
        $segments = explode('/', $this->currentPath);
        array_pop($segments);
        $this->currentPath = implode('/', array_filter($segments));
        $this->reset(['selectedImage', 'renamingPath', 'newName', 'editingFile', 'fileContent']);
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
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), ['txt', 'md', 'log'])) {
            $this->editingFile = $path;
            $this->fileContent = Storage::disk('public')->get($path);
        } else {
            $this->dispatch('toast', 'فقط فایل‌های متنی قابل ویرایش هستند.', ['type' => 'error']);
        }
    }

    public function saveFile()
    {
        Storage::disk('public')->put($this->editingFile, $this->fileContent);
        $this->editingFile = null;
        $this->fileContent = '';
        $this->dispatch('toast', 'فایل با موفقیت ذخیره شد.', ['type' => 'success']);
    }

    public function closeEditor()
    {
        if ($this->fileContent !== Storage::disk('public')->get($this->editingFile)) {
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

    public function render()
    {
        $fullPath = $this->currentPath ? $this->currentPath : '';
        $directories = Storage::disk('public')->directories($fullPath);
        $files = Storage::disk('public')->files($fullPath);

        $items = [];
        foreach ($directories as $dir) {
            $name = basename($dir);
            if ($this->search === '' || stripos($name, $this->search) !== false) {
                $items[] = [
                    'type' => 'folder',
                    'name' => $name,
                    'path' => $dir,
                ];
            }
        }
        foreach ($files as $file) {
            $name = basename($file);
            if ($this->search === '' || stripos($name, $this->search) !== false) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $items[] = [
                    'type' => 'file',
                    'name' => $name,
                    'path' => $file,
                    'url' => asset('storage/' . $file),
                    'isImage' => in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']),
                    'isText' => in_array(strtolower($extension), ['txt', 'md', 'log']),
                ];
            }
        }

        return view('livewire.admin.panel.tools.file-manager', [
            'items' => $items,
        ]);
    }
}