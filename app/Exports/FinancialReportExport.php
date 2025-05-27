<?php

namespace App\Exports;

use App\Models\DoctorWalletTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DoctorWalletTransaction::where('doctor_id', Auth::guard('doctor')->user()->id  ?? Auth::guard('secretary')->user()->doctor_id);

        // Apply filters
        if (!empty($this->filters['search'])) {
            $query->where('description', 'like', '%' . $this->filters['search'] . '%');
        }
        if (!empty($this->filters['transactionType'])) {
            $query->where('type', $this->filters['transactionType']);
        }
        if (!empty($this->filters['transactionStatus'])) {
            $query->where('status', $this->filters['transactionStatus']);
        }
        if (!empty($this->filters['clinicId'])) {
            $query->where('clinic_id', $this->filters['clinicId']);
        }
        if (!empty($this->filters['minAmount'])) {
            $query->where('amount', '>=', $this->filters['minAmount']);
        }
        if (!empty($this->filters['maxAmount'])) {
            $query->where('amount', '<=', $this->filters['maxAmount']);
        }
        if (!empty($this->filters['startDate']) && !empty($this->filters['endDate'])) {
            $query->whereBetween('registered_at', [
                \Carbon\Carbon::parse($this->filters['startDate'])->startOfDay(),
                \Carbon\Carbon::parse($this->filters['endDate'])->endOfDay(),
            ]);
        }

        return $query->orderBy('registered_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ردیف',
            'تاریخ',
            'کلینیک',
            'نوع تراکنش',
            'وضعیت',
            'مبلغ (ریال)',
            'توضیحات',
        ];
    }

    public function map($transaction): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            \Carbon\Carbon::parse($transaction->registered_at)->format('Y-m-d H:i'),
            $transaction->clinic ? $transaction->clinic->name : 'بدون کلینیک',
            $transaction->type === 'online' ? 'آنلاین' : ($transaction->type === 'in_person' ? 'حضوری' : 'شارژ'),
            $transaction->status === 'pending' ? 'در انتظار' : ($transaction->status === 'available' ? 'موجود' : ($transaction->status === 'requested' ? 'درخواست‌شده' : 'پرداخت‌شده')),
            number_format($transaction->amount),
            $transaction->description ?? 'بدون توضیح',
        ];
    }
}
