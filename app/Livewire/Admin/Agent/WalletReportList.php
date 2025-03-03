<?php

namespace App\Livewire\Admin\Agent;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Agent\WalletReport;

class WalletReportList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedReports = [];
    public $selectAll = false;
    public $perPage = 10;
    public $reportStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadReportStatuses();
    }

    public function updatedSearch()
    {
        Log::info('Search Query Updated:', ['search' => $this->search]);
        $this->resetPage();
        $this->loadReportStatuses();
    }

    public function updatedSelectAll($value)
    {
        Log::info('Select All Updated:', ['value' => $value]);
        if ($value) {
            $this->selectedReports = WalletReport::where('description', 'like', '%' . $this->search . '%')
                ->orWhere('report_date', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedReports = [];
        }
        Log::info('Selected Reports:', ['selectedReports' => $this->selectedReports]);
    }

    public function updatedSelectedReports()
    {
        Log::info('Selected Reports Updated:', ['selectedReports' => $this->selectedReports]);
        $total = WalletReport::where('description', 'like', '%' . $this->search . '%')
            ->orWhere('report_date', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedReports) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $report = WalletReport::find($id);
        if ($report) {
            $report->status = $report->status === 'در انتظار درخواست' ? 'پرداخت‌شده' : 'در انتظار درخواست';
            $report->save();
            $this->reportStatuses[$id] = $report->status;
            Log::info('Report status toggled', ['id' => $id, 'status' => $report->status]);
            $this->dispatch('toast', 'وضعیت گزارش با موفقیت تغییر کرد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedReports)) {
            Log::info('No reports selected for deletion');
            $this->dispatch('toast', 'هیچ گزارشی انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            WalletReport::whereIn('id', $this->selectedReports)->delete();
            $this->selectedReports = [];
            $this->selectAll = false;
            Log::info('Wallet reports deleted', ['ids' => $this->selectedReports]);
            $this->dispatch('toast', 'گزارش‌های انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadReportStatuses();
        } catch (\Exception $e) {
            Log::error('Error deleting wallet reports:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف گزارش‌ها: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadReportStatuses()
    {
        $reports = WalletReport::where('description', 'like', '%' . $this->search . '%')
            ->orWhere('report_date', 'like', '%' . $this->search . '%')
            ->get();
        foreach ($reports as $report) {
            $this->reportStatuses[$report->id] = $report->status;
        }
    }

    public function render()
    {
        $reports = WalletReport::where('description', 'like', '%' . $this->search . '%')
            ->orWhere('report_date', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        // تبدیل تاریخ‌ها به فارسی
        foreach ($reports as $report) {
            $report->persian_date = $this->toPersianDate($report->report_date);
        }

        return view('livewire.admin.agent.wallet-report-list', [
            'reports' => $reports,
        ])->extends('admin.content.layouts.layoutMaster');
    }

    // متد کمکی برای تبدیل تاریخ به فارسی
    public function toPersianDate($date)
    {
        // اگه پکیج morilog/jalali داری:
        if (class_exists(\Morilog\Jalali\Jalalian::class)) {
            return \Morilog\Jalali\Jalalian::fromDateTime($date)->format('Y/m/d H:i');
        }

        // روش دستی:
        $gregorian = strtotime($date);
        $gYear = (int) date('Y', $gregorian);
        $gMonth = (int) date('m', $gregorian);
        $gDay = (int) date('d', $gregorian);
        $gHour = date('H', $gregorian);
        $gMinute = date('i', $gregorian);

        $jd = gregoriantojd($gMonth, $gDay, $gYear);
        $jYear = $gYear - 621;
        $jDays = $jd - gregoriantojd(3, 21, $gYear); // فاصله از نوروز
        if ($jDays < 0) {
            $jYear--;
            $jDays += 186 + 179;
        }

        $jMonth = $jDays < 186 ? ceil($jDays / 31) : ceil(($jDays - 186) / 30) + 6;
        $jDay = $jDays < 186 ? ($jDays % 31) : (($jDays - 186) % 30);
        if ($jDay == 0) {
            $jDay = $jMonth <= 6 ? 31 : 30;
            $jMonth--;
        }

        return sprintf('%04d/%02d/%02d %02d:%02d', $jYear, $jMonth, $jDay, $gHour, $gMinute);
    }
}