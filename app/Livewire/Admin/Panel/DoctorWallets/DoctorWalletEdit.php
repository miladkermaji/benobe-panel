<?php

namespace App\Livewire\Admin\Panel\DoctorWallets;

use Livewire\Component;
use App\Models\DoctorWallet;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DoctorWalletEdit extends Component
{
    use WithFileUploads;

    public $doctorwallet;
    public $form = [];
    public $photo;
    public $photoPreview;

    public function mount($id)
    {
        $this->doctorwallet = DoctorWallet::findOrFail($id);
        $this->form = $this->doctorwallet->toArray();
        $this->photoPreview = $this->doctorwallet->photo ? Storage::url($this->doctorwallet->photo) : asset('default-avatar.png');
    }

    public function updatedPhoto()
    {
        $this->photoPreview = $this->photo->temporaryUrl();
    }

    public function update()
    {
        $validator = Validator::make(
            array_merge($this->form, ['photo' => $this->photo]),
            [
                'form.name' => 'required|string|max:255',
                'form.description' => 'nullable|string|max:500',
                'form.status' => 'required|boolean',
                'photo' => 'nullable|image|max:2048',
            ]
        );

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $data = $this->form;

        if ($this->photo) {
            $data['photo'] = $this->photo->store('photos', 'public');
        }

        $this->doctorwallet->update($data);

        $this->dispatch('show-alert', type: 'success', message: 'doctorwallet با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.doctor-wallets.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctor-wallets.doctor-wallet-edit');
    }
}