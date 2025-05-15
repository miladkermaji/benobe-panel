<?php

namespace App\Livewire\Dr\Panel\Financial;

use App\Models\Clinic;
use App\Models\DoctorWalletTransaction;
use App\Models\Insurance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

class FinancialReport extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFilter = 'daily';
    public $transactionType = '';
    public $transactionStatus = '';
    public $clinicId = '';
    public $minAmount = '';
    public $maxAmount = '';
    public $startDate;
    public $endDate;
    public $paymentMethod = '';
    public $insuranceId = '';
    public $perPage = 10;
    public $readyToLoad = false;
    public $chartData = [];
    public $summary = [
        'daily' => 0,
        'weekly' => 0,
        'monthly' => 0,
        'yearly' => 0,
        'total' => 0,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFilter' => ['except' => 'daily'],
        'transactionType' => ['except' => ''],
        'transactionStatus' => ['except' => ''],
        'clinicId' => ['except' => ''],
        'minAmount' => ['except' => ''],
        'maxAmount' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'paymentMethod' => ['except' => ''],
        'insuranceId' => ['except' => ''],
    ];

    public function mount()
    {
        $this->startDate = Jalalian::now()->format('Y/m/d');
        $this->endDate = Jalalian::now()->format('Y/m/d');
        $this->readyToLoad = false; // Lazy loading
        Log::info('FinancialReport component mounted', ['doctor_id' => Auth::guard('doctor')->user()->id]);
    }

    public function loadReports()
    {
        $this->readyToLoad = true;
        $this->updateChartData();
        $this->updateSummary();
        Log::info('Reports loaded', ['doctor_id' => Auth::guard('doctor')->user()->id, 'filters' => $this->getFilters()]);
    }

    public function updated($propertyName)
    {
        Log::debug("Property updated: {$propertyName}", ['value' => $this->$propertyName]);
        if ($propertyName === 'dateFilter') {
            $this->updateDateRange();
        }
        $this->resetPage();
        if ($this->readyToLoad) {
            $this->updateChartData();
            $this->updateSummary();
        }
    }

    private function updateDateRange()
    {
        switch ($this->dateFilter) {
            case 'daily':
                $this->startDate = Jalalian::now()->format('Y/m/d');
                $this->endDate = Jalalian::now()->format('Y/m/d');
                break;
            case 'weekly':
                $this->startDate = Jalalian::now()->subDays(6)->format('Y/m/d');
                $this->endDate = Jalalian::now()->format('Y/m/d');
                break;
            case 'monthly':
                $this->startDate = Jalalian::now()->subMonths(1)->format('Y/m/d');
                $this->endDate = Jalalian::now()->format('Y/m/d');
                break;
            case 'yearly':
                $this->startDate = Jalalian::now()->subYears(1)->format('Y/m/d');
                $this->endDate = Jalalian::now()->format('Y/m/d');
                break;
            case 'custom':
                $this->startDate = $this->startDate ?: Jalalian::now()->format('Y/m/d');
                $this->endDate = $this->endDate ?: Jalalian::now()->format('Y/m/d');
                break;
            default:
                $this->startDate = null;
                $this->endDate = null;
        }
        Log::info('Date range updated', ['dateFilter' => $this->dateFilter, 'startDate' => $this->startDate, 'endDate' => $this->endDate]);
    }

    private function updateChartData()
    {
        if (!$this->readyToLoad) {
            return;
        }

        $query = DoctorWalletTransaction::where('doctor_id', Auth::guard('doctor')->user()->id);
        $this->applyFilters($query);

        $data = $query->selectRaw('DATE(registered_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $this->chartData = [
            'labels' => $data->pluck('date')->map(fn ($date) => Jalalian::fromCarbon(Carbon::parse($date))->format('Y/m/d'))->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];

        $this->dispatch('updateChart', $this->chartData);
        Log::info('Chart data updated', ['chartData' => $this->chartData]);
    }

    private function updateSummary()
    {
        if (!$this->readyToLoad) {
            return;
        }

        $doctorId = Auth::guard('doctor')->user()->id;

        $this->summary = [
            'daily' => DoctorWalletTransaction::where('doctor_id', $doctorId)
                ->whereDate('registered_at', Carbon::today())
                ->sum('amount'),
            'weekly' => DoctorWalletTransaction::where('doctor_id', $doctorId)
                ->whereBetween('registered_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('amount'),
            'monthly' => DoctorWalletTransaction::where('doctor_id', $doctorId)
                ->whereBetween('registered_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->sum('amount'),
            'yearly' => DoctorWalletTransaction::where('doctor_id', $doctorId)
                ->whereBetween('registered_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
                ->sum('amount'),
            'total' => DoctorWalletTransaction::where('doctor_id', $doctorId)
                ->sum('amount'),
        ];
    }

    private function getFilters()
    {
        return [
            'search' => $this->search,
            'dateFilter' => $this->dateFilter,
            'transactionType' => $this->transactionType,
            'transactionStatus' => $this->transactionStatus,
            'clinicId' => $this->clinicId,
            'minAmount' => $this->minAmount,
            'maxAmount' => $this->maxAmount,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'paymentMethod' => $this->paymentMethod,
            'insuranceId' => $this->insuranceId,
        ];
    }

    private function applyFilters($query)
    {
        if ($this->search) {
            $query->where('description', 'like', '%' . $this->search . '%');
        }
        if ($this->transactionType) {
            $query->where('type', $this->transactionType);
        }
        if ($this->transactionStatus) {
            $query->where('status', $this->transactionStatus);
        }
        if ($this->clinicId) {
            $query->where('clinic_id', $this->clinicId);
        }
        if ($this->minAmount) {
            $query->where('amount', '>=', $this->minAmount);
        }
        if ($this->maxAmount) {
            $query->where('amount', '<=', $this->maxAmount);
        }
        if ($this->startDate && $this->endDate && $this->dateFilter !== 'all') {
            $start = Carbon::createFromFormat('Y/m/d', $this->startDate)->startOfDay();
            $end = Carbon::createFromFormat('Y/m/d', $this->endDate)->endOfDay();
            $query->whereBetween('registered_at', [$start, $end]);
        }
        if ($this->paymentMethod) {
            $query->whereHas('appointment', fn ($q) => $q->where('payment_method', $this->paymentMethod));
        }
        if ($this->insuranceId) {
            $query->whereHas('appointment', fn ($q) => $q->where('insurance_id', $this->insuranceId));
        }
    }

    public function exportExcel()
    {
        Log::info('Excel export initiated', ['doctor_id' => Auth::guard('doctor')->user()->id, 'filters' => $this->getFilters()]);
        return redirect()->route('dr.panel.financial-reports.export-excel', $this->getFilters());
    }

    public function exportPdf()
    {
        Log::info('PDF export initiated', ['doctor_id' => Auth::guard('doctor')->user()->id, 'filters' => $this->getFilters()]);
        return redirect()->route('dr.panel.financial-reports.export-pdf', $this->getFilters());
    }

    private function getTransactions()
    {
        $query = DoctorWalletTransaction::where('doctor_id', Auth::guard('doctor')->user()->id)
            ->with(['clinic', 'appointment', 'appointment.insurance']);
        $this->applyFilters($query);
        return $query->orderBy('registered_at', 'desc');
    }

    public function formatPaymentMethod($method)
    {
        switch ($method) {
            case 'online': return 'آنلاین';
            case 'cash': return 'نقدی';
            case 'card_to_card': return 'کارت به کارت';
            case 'pos': return 'POS';
            default: return $method;
        }
    }

    public function render()
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $transactions = $this->readyToLoad ? $this->getTransactions()->paginate($this->perPage) : collect([]);
        $totalAmount = $this->readyToLoad ? $this->getTransactions()->sum('amount') : 0;
        $clinics = Clinic::where('doctor_id', $doctorId)->get();
        $insurances = Insurance::whereHas('doctors', fn ($q) => $q->where('doctor_id', $doctorId))->get();
    
        Log::info('Rendering FinancialReport view', [
            'doctor_id' => $doctorId,
            'transaction_count' => $this->readyToLoad ? $transactions->total() : 0, // اصلاح خطا
            'total_amount' => $totalAmount,
        ]);
    
        return view('livewire.dr.panel.financial.financial-report', [
            'transactions' => $transactions,
            'totalAmount' => $totalAmount,
            'clinics' => $clinics,
            'insurances' => $insurances,
            'summary' => $this->summary,
        ]);
    }
}
