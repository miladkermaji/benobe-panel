<?php

namespace App\Livewire\Admin\Panel\Transactions;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Manager;

class TransactionEdit extends Component
{
    public $transaction;
    public $transactable_type;
    public $transactable_id;
    public $amount;
    public $gateway;
    public $status;
    public $transaction_id;
    public $type; // فیلد جدید برای نوع تراکنش
    public $description; // فیلد جدید برای توضیحات
    public $users;
    public $doctors;
    public $secretaries;
    public $managers;
    public $entities = [
        'App\Models\User' => 'کاربر',
        'App\Models\Doctor' => 'دکتر',
        'App\Models\Secretary' => 'منشی',
        'App\Models\Admin\Manager' => 'مدیر',
    ];

    public function mount($id)
    {
        $this->transaction = Transaction::findOrFail($id);
        $this->transactable_type = $this->transaction->transactable_type;
        $this->transactable_id = $this->transaction->transactable_id;
        $this->amount = $this->transaction->amount;
        $this->gateway = $this->transaction->gateway;
        $this->status = $this->transaction->status;
        $this->transaction_id = $this->transaction->transaction_id;

        // بارگذاری meta
        $meta = $this->transaction->meta ? json_decode($this->transaction->meta, true) : [];
        $this->type = $meta['type'] ?? '';
        $this->description = $meta['description'] ?? '';

        $this->users = User::all();
        $this->doctors = Doctor::all();
        $this->secretaries = Secretary::all();
        $this->managers = Manager::all();
    }

    public function update()
    {
        $validator = Validator::make([
            'transactable_type' => $this->transactable_type,
            'transactable_id' => $this->transactable_id,
            'amount' => $this->amount,
            'gateway' => $this->gateway,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'type' => $this->type,
            'description' => $this->description,
        ], [
            'transactable_type' => 'required|in:' . implode(',', array_keys($this->entities)),
            'transactable_id' => 'required|numeric|exists:' . $this->transactable_type . ',id',
            'amount' => 'required|numeric|min:0',
            'gateway' => 'required|string|max:255',
            'status' => 'required|in:pending,paid,failed',
            'transaction_id' => 'nullable|string|max:255',
            'type' => 'required|in:wallet_charge,profile_upgrade,other',
            'description' => 'nullable|string|max:255',
        ], [
            'transactable_type.required' => 'نوع موجودیت الزامی است.',
            'transactable_id.required' => 'شناسه موجودیت الزامی است.',
            'amount.required' => 'مبلغ تراکنش الزامی است.',
            'gateway.required' => 'نام درگاه پرداخت الزامی است.',
            'status.required' => 'وضعیت تراکنش الزامی است.',
            'type.required' => 'نوع تراکنش الزامی است.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $meta = [
            'type' => $this->type,
            'description' => $this->description ?: 'بدون توضیحات',
        ];

        // اضافه کردن doctor_id فقط اگه نوع دکتر باشه
        if ($this->transactable_type === 'App\Models\Doctor') {
            $meta['doctor_id'] = (int) $this->transactable_id;
        }

        $this->transaction->update([
            'transactable_type' => $this->transactable_type,
            'transactable_id' => $this->transactable_id,
            'amount' => $this->amount,
            'gateway' => $this->gateway,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'meta' => json_encode($meta),
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'تراکنش با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.transactions.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.transactions.transaction-edit');
    }
}
