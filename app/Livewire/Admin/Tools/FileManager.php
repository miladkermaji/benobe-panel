<?php

namespace App\Livewire\Admin\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
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

    protected $rules = [
        'newFolderName' => 'required|string|max:255',
        'filesToUpload.*' => 'file|max:10240', // حداکثر 10MB
        'newName' => 'required|string|max:255',
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
        }
        $this->filesToUpload = [];
        $this->dispatch('toast', 'فایل‌ها با موفقیت آپلود شدند.', ['type' => 'success']);
    }

    public function deleteItem($path)
    {
        if (Storage::disk('public')->exists($path)) {
            if (Storage::disk('public')->directoryExists($path)) {
                Storage::disk('public')->deleteDirectory($path);
            } else {
                Storage::disk('public')->delete($path);
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
        $this->search = '';
        $this->selectedImage = null;
        $this->renamingPath = null;
        $this->newName = '';
    }

    public function goBack()
    {
        $segments = explode('/', $this->currentPath);
        array_pop($segments);
        $this->currentPath = implode('/', array_filter($segments));
        $this->selectedImage = null;
        $this->renamingPath = null;
        $this->newName = '';
    }

    public function selectImage($url)
    {
        $this->selectedImage = $url;
    }

    public function closePreview()
    {
        $this->selectedImage = null;
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
                $items[] = [
                    'type' => 'file',
                    'name' => $name,
                    'path' => $file,
                    'url' => asset('storage/' . $file),
                    'isImage' => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']),
                ];
            }
        }

        return view('livewire.admin.tools.file-manager', [
            'items' => $items,
        ]);
    }
}