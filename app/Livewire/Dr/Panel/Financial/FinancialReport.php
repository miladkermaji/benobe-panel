<?php

namespace App\Livewire\Dr\Panel\Financial;

use App\Models\Clinic;
use Livewire\Component;
use App\Models\Insurance;
use App\Models\Appointment;
use App\Models\Transaction;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Carbon;
use App\Models\ManualAppointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\CounselingAppointment;
use App\Models\DoctorWalletTransaction;

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
    public $page = 1;

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
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->startDate = Jalalian::now()->format('Y/m/d');
        $this->endDate = Jalalian::now()->format('Y/m/d');
        $this->readyToLoad = true; // فوراً داده‌ها لود بشن
        $this->updateDateRange();
        $this->updateChartData();
        $this->updateSummary();
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
                $this->startDate = Jalalian::now()->subDays(30)->format('Y/m/d');
                $this->endDate = Jalalian::now()->format('Y/m/d');
                break;
            case 'yearly':
                $this->startDate = Jalalian::now()->subYears(1)->format('Y/m/d');
                $this->endDate = Jalalian::now()->format('Y/m/d');
                break;
            case 'custom':
                $this->startDate = $this->startDate ?: Jalalian::now()->subDays(30)->format('Y/m/d');
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

        $doctorId = Auth::guard('doctor')->user()->id;

        // تبدیل تاریخ‌های جلالی به میلادی
        try {
            $start = $this->startDate && $this->dateFilter !== 'all'
                ? Jalalian::fromFormat('Y/m/d', $this->startDate)->toCarbon()->startOfDay()
                : Carbon::now()->subYears(1);
            $end = $this->endDate && $this->dateFilter !== 'all'
                ? Jalalian::fromFormat('Y/m/d', $this->endDate)->toCarbon()->endOfDay()
                : Carbon::now();
        } catch (\Exception $e) {
            Log::error('Error converting Jalali dates', ['error' => $e->getMessage(), 'startDate' => $this->startDate, 'endDate' => $this->endDate]);
            $start = Carbon::now()->subYears(1);
            $end = Carbon::now();
        }

        Log::info('Chart data date range', ['start' => $start, 'end' => $end]);

        // کوئری برای ترکیب داده‌ها
        $transactionsQuery = Transaction::whereJsonContains('meta->doctor_id', $doctorId)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date');

        $walletTransactionsQuery = DoctorWalletTransaction::where('doctor_id', $doctorId)
            ->selectRaw('DATE(registered_at) as date, SUM(amount) as total')
            ->whereBetween('registered_at', [$start, $end])
            ->groupBy('date');

        $appointmentsQuery = Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(appointment_date) as date, SUM(final_price) as total')
            ->whereBetween('appointment_date', [$start, $end])
            ->groupBy('date');

        $manualAppointmentsQuery = ManualAppointment::where('doctor_id', $doctorId)
            ->whereNotNull('final_price')
            ->selectRaw('DATE(appointment_date) as date, SUM(final_price) as total')
            ->whereBetween('appointment_date', [$start, $end])
            ->groupBy('date');

        $counselingAppointmentsQuery = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(appointment_date) as date, SUM(final_price) as total')
            ->whereBetween('appointment_date', [$start, $end])
            ->groupBy('date');

        // اعمال فیلترها
        $this->applyFilters($transactionsQuery, 'Transaction');
        $this->applyFilters($walletTransactionsQuery, 'DoctorWalletTransaction');
        $this->applyFilters($appointmentsQuery, 'Appointment');
        $this->applyFilters($manualAppointmentsQuery, 'ManualAppointment');
        $this->applyFilters($counselingAppointmentsQuery, 'CounselingAppointment');

        // لاگ کوئری‌ها
        Log::info('Transactions Query', ['sql' => $transactionsQuery->toSql(), 'bindings' => $transactionsQuery->getBindings()]);
        Log::info('Wallet Transactions Query', ['sql' => $walletTransactionsQuery->toSql(), 'bindings' => $walletTransactionsQuery->getBindings()]);
        Log::info('Appointments Query', ['sql' => $appointmentsQuery->toSql(), 'bindings' => $appointmentsQuery->getBindings()]);
        Log::info('Manual Appointments Query', ['sql' => $manualAppointmentsQuery->toSql(), 'bindings' => $manualAppointmentsQuery->getBindings()]);
        Log::info('Counseling Appointments Query', ['sql' => $counselingAppointmentsQuery->toSql(), 'bindings' => $counselingAppointmentsQuery->getBindings()]);

        // ترکیب نتایج
        $data = $transactionsQuery->get()
            ->merge($walletTransactionsQuery->get())
            ->merge($appointmentsQuery->get())
            ->merge($manualAppointmentsQuery->get())
            ->merge($counselingAppointmentsQuery->get())
            ->groupBy('date')
            ->map(function ($group) {
                return [
                    'date' => $group->first()->date,
                    'total' => $group->sum('total'),
                ];
            })
            ->sortBy('date')
            ->values();

        // بررسی خالی بودن داده‌ها
        if ($data->isEmpty()) {
            Log::warning('No chart data found for range', ['start' => $start, 'end' => $end]);
            $this->chartData = ['labels' => [], 'values' => []];
            $this->dispatch('updateChart', $this->chartData);
            return;
        }

        $this->chartData = [
            'labels' => $data->pluck('date')->map(fn ($date) => Jalalian::fromCarbon(Carbon::parse($date))->format('Y/m/d'))->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];

        Log::info('Chart data prepared', ['labels' => $this->chartData['labels'], 'values' => $this->chartData['values']]);

        $this->dispatch('updateChart', $this->chartData);
    }

    private function updateSummary()
    {
        if (!$this->readyToLoad) {
            return;
        }

        $doctorId = Auth::guard('doctor')->user()->id;

        $this->summary = [
            'daily' => $this->getTotalAmount(Carbon::today(), Carbon::today()->endOfDay()),
            'weekly' => $this->getTotalAmount(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()),
            'monthly' => $this->getTotalAmount(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()),
            'yearly' => $this->getTotalAmount(Carbon::now()->startOfYear(), Carbon::now()->endOfYear()),
            'total' => $this->getTotalAmount(null, null),
        ];

        Log::info('Summary updated', ['summary' => $this->summary]);
    }

    private function getTotalAmount($start = null, $end = null)
    {
        $doctorId = Auth::guard('doctor')->user()->id;

        $transactionsTotal = Transaction::whereJsonContains('meta->doctor_id', $doctorId);
        $walletTransactionsTotal = DoctorWalletTransaction::where('doctor_id', $doctorId);
        $appointmentsTotal = Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid');
        $manualAppointmentsTotal = ManualAppointment::where('doctor_id', $doctorId)
            ->whereNotNull('final_price');
        $counselingAppointmentsTotal = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid');

        if ($start && $end) {
            $transactionsTotal->whereBetween('created_at', [$start, $end]);
            $walletTransactionsTotal->whereBetween('registered_at', [$start, $end]);
            $appointmentsTotal->whereBetween('appointment_date', [$start, $end]);
            $manualAppointmentsTotal->whereBetween('appointment_date', [$start, $end]);
            $counselingAppointmentsTotal->whereBetween('appointment_date', [$start, $end]);
        }

        // اعمال فیلترها
        $this->applyFilters($transactionsTotal, 'Transaction');
        $this->applyFilters($walletTransactionsTotal, 'DoctorWalletTransaction');
        $this->applyFilters($appointmentsTotal, 'Appointment');
        $this->applyFilters($manualAppointmentsTotal, 'ManualAppointment');
        $this->applyFilters($counselingAppointmentsTotal, 'CounselingAppointment');

        $total = $transactionsTotal->sum('amount')
            + $walletTransactionsTotal->sum('amount')
            + $appointmentsTotal->sum('final_price')
            + $manualAppointmentsTotal->sum('final_price')
            + $counselingAppointmentsTotal->sum('final_price');

        Log::info('Total amount calculated', [
            'transactions' => $transactionsTotal->sum('amount'),
            'wallet_transactions' => $walletTransactionsTotal->sum('amount'),
            'appointments' => $appointmentsTotal->sum('final_price'),
            'manual_appointments' => $manualAppointmentsTotal->sum('final_price'),
            'counseling_appointments' => $counselingAppointmentsTotal->sum('final_price'),
            'total' => $total,
        ]);

        return $total;
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

    private function applyFilters($query, $modelType)
    {
        if ($this->search) {
            if ($modelType === 'Transaction') {
                $query->where('meta', 'like', '%' . $this->search . '%');
            } elseif ($modelType === 'DoctorWalletTransaction') {
                $query->where('description', 'like', '%' . $this->search . '%');
            } else {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhere('title', 'like', '%' . $this->search . '%');
                });
            }
        }

        if ($this->transactionType) {
            if ($modelType === 'Transaction') {
                $query->whereJsonContains('meta->type', $this->transactionType);
            } elseif ($modelType === 'DoctorWalletTransaction') {
                $query->where('type', $this->transactionType);
            } elseif ($modelType === 'Appointment' || $modelType === 'CounselingAppointment') {
                $query->where('appointment_type', $this->transactionType);
            } elseif ($modelType === 'ManualAppointment') {
                $query->where('payment_method', $this->transactionType);
            }
        }

        if ($this->transactionStatus) {
            if ($modelType === 'Transaction') {
                $query->where('status', $this->transactionStatus);
            } elseif ($modelType === 'DoctorWalletTransaction') {
                $query->where('status', $this->transactionStatus);
            } elseif ($modelType === 'Appointment' || $modelType === 'CounselingAppointment') {
                $query->where('payment_status', $this->transactionStatus);
            } elseif ($modelType === 'ManualAppointment') {
                $query->where('status', $this->transactionStatus);
            }
        }

        if ($this->clinicId) {
            if ($modelType === 'Transaction') {
                $query->whereJsonContains('meta->clinic_id', (int) $this->clinicId);
            } else {
                $query->where('clinic_id', $this->clinicId);
            }
        }

        if ($this->minAmount) {
            if ($modelType === 'Transaction' || $modelType === 'DoctorWalletTransaction') {
                $query->where('amount', '>=', $this->minAmount);
            } else {
                $query->where('final_price', '>=', $this->minAmount);
            }
        }

        if ($this->maxAmount) {
            if ($modelType === 'Transaction' || $modelType === 'DoctorWalletTransaction') {
                $query->where('amount', '<=', $this->maxAmount);
            } else {
                $query->where('final_price', '<=', $this->maxAmount);
            }
        }

        // اعمال شرط زمانی فقط برای ستون مناسب هر مدل
        if ($this->startDate && $this->endDate && $this->dateFilter !== 'all') {
            try {
                $start = Jalalian::fromFormat('Y/m/d', $this->startDate)->toCarbon()->startOfDay();
                $end = Jalalian::fromFormat('Y/m/d', $this->endDate)->toCarbon()->endOfDay();
                if ($modelType === 'Transaction') {
                    if (!collect($query->getQuery()->wheres)->contains('column', 'created_at')) {
                        $query->whereBetween('created_at', [$start, $end]);
                    }
                } elseif ($modelType === 'DoctorWalletTransaction') {
                    if (!collect($query->getQuery()->wheres)->contains('column', 'registered_at')) {
                        $query->whereBetween('registered_at', [$start, $end]);
                    }
                } elseif (in_array($modelType, ['Appointment', 'ManualAppointment', 'CounselingAppointment'])) {
                    if (!collect($query->getQuery()->wheres)->contains('column', 'appointment_date')) {
                        $query->whereBetween('appointment_date', [$start, $end]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error applying date filter', ['error' => $e->getMessage(), 'startDate' => $this->startDate, 'endDate' => $this->endDate]);
            }
        }

        if ($this->paymentMethod) {
            if ($modelType === 'Transaction') {
                $query->whereJsonContains('meta->payment_method', $this->paymentMethod);
            } elseif ($modelType === 'Appointment' || $modelType === 'ManualAppointment') {
                $query->where('payment_method', $this->paymentMethod);
            }
        }

        if ($this->insuranceId) {
            if ($modelType === 'Transaction') {
                $query->whereJsonContains('meta->insurance_id', (int) $this->insuranceId);
            } elseif ($modelType === 'Appointment' || $modelType === 'CounselingAppointment') {
                $query->where('insurance_id', $this->insuranceId);
            }
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

        // گرفتن داده‌های تراکنش‌ها
        $transactions = $this->getTransactions()->items();

        // ذخیره داده‌ها و فیلترها در session
        session([
            'financial_report_data' => $transactions,
            'financial_report_filters' => $this->getFilters()
        ]);

        return redirect()->route('dr.panel.financial-reports.export-pdf', $this->getFilters());
    }

    private function getTransactions()
    {
        $doctorId = Auth::guard('doctor')->user()->id;

        $transactionsQuery = Transaction::whereJsonContains('meta->doctor_id', $doctorId);
        $walletTransactionsQuery = DoctorWalletTransaction::where('doctor_id', $doctorId);
        $appointmentsQuery = Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid');
        $manualAppointmentsQuery = ManualAppointment::where('doctor_id', $doctorId)
            ->whereNotNull('final_price');
        $counselingAppointmentsQuery = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid');

        $this->applyFilters($transactionsQuery, 'Transaction');
        $this->applyFilters($walletTransactionsQuery, 'DoctorWalletTransaction');
        $this->applyFilters($appointmentsQuery, 'Appointment');
        $this->applyFilters($manualAppointmentsQuery, 'ManualAppointment');
        $this->applyFilters($counselingAppointmentsQuery, 'CounselingAppointment');

        // استخراج و فرمت داده‌ها
        $transactions = $transactionsQuery->get()->map(function ($item) {
            $meta = json_decode($item->meta, true);
            return [
                'id' => $item->id,
                'type' => 'transaction',
                'date' => $item->created_at,
                'amount' => $item->amount,
                'status' => $item->status,
                'description' => $meta['description'] ?? 'بدون توضیح',
                'clinic_id' => $meta['clinic_id'] ?? null,
                'payment_method' => $meta['payment_method'] ?? null,
                'insurance_id' => $meta['insurance_id'] ?? null,
                'transaction_type' => $meta['type'] ?? 'unknown',
            ];
        });

        $walletTransactions = $walletTransactionsQuery->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => 'wallet_transaction',
                'date' => $item->registered_at,
                'amount' => $item->amount,
                'status' => $item->status,
                'description' => $item->description ?? 'بدون توضیح',
                'clinic_id' => $item->clinic_id,
                'payment_method' => null,
                'insurance_id' => null,
                'transaction_type' => $item->type,
            ];
        });

        $appointments = $appointmentsQuery->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => 'appointment',
                'date' => $item->appointment_date,
                'amount' => $item->final_price,
                'status' => $item->payment_status,
                'description' => $item->description ?? 'بدون توضیح',
                'clinic_id' => $item->clinic_id,
                'payment_method' => $item->payment_method,
                'insurance_id' => $item->insurance_id,
                'transaction_type' => $item->appointment_type,
            ];
        });

        $manualAppointments = $manualAppointmentsQuery->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => 'manual_appointment',
                'date' => $item->appointment_date,
                'amount' => $item->final_price,
                'status' => $item->status,
                'description' => $item->description ?? 'بدون توضیح',
                'clinic_id' => $item->clinic_id,
                'payment_method' => $item->payment_method,
                'insurance_id' => null,
                'transaction_type' => $item->payment_method ?? 'manual',
            ];
        });

        $counselingAppointments = $counselingAppointmentsQuery->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => 'counseling_appointment',
                'date' => $item->appointment_date,
                'amount' => $item->final_price,
                'status' => $item->payment_status,
                'description' => $item->description ?? 'بدون توضیح',
                'clinic_id' => $item->clinic_id,
                'payment_method' => null, // CounselingAppointment ستون payment_method نداره
                'insurance_id' => $item->insurance_id,
                'transaction_type' => $item->appointment_type,
            ];
        });

        // ادغام و مرتب‌سازی نتایج
        $allTransactions = collect()
            ->concat($transactions)
            ->concat($walletTransactions)
            ->concat($appointments)
            ->concat($manualAppointments)
            ->concat($counselingAppointments)
            ->sortByDesc('date');

        // لاگ تعداد تراکنش‌ها
        Log::info('Transactions retrieved', [
            'transactions_count' => $transactions->count(),
            'wallet_transactions_count' => $walletTransactions->count(),
            'appointments_count' => $appointments->count(),
            'manual_appointments_count' => $manualAppointments->count(),
            'counseling_appointments_count' => $counselingAppointments->count(),
            'total_count' => $allTransactions->count(),
        ]);

        // صفحه‌بندی دستی
        $perPage = $this->perPage;
        $currentPage = $this->page;
        $paginated = $allTransactions->forPage($currentPage, $perPage);
        $total = $allTransactions->count();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            $total,
            $perPage,
            $currentPage,
            ['path' => url()->current()]
        );
    }

    public function formatPaymentMethod($method)
    {
        switch ($method) {
            case 'online': return 'آنلاین';
            case 'cash': return 'نقدی';
            case 'card_to_card': return 'کارت به کارت';
            case 'pos': return 'POS';
            case 'card': return 'کارت';
            case 'insurance': return 'بیمه';
            default: return $method ?: '-';
        }
    }

    public function formatTransactionType($type)
    {
        switch ($type) {
            case 'wallet_charge': return 'شارژ کیف پول';
            case 'profile_upgrade': return 'ارتقای حساب';
            case 'appointment': return 'نوبت‌دهی';
            case 'online': return 'آنلاین';
            case 'in_person': return 'حضوری';
            case 'charge': return 'شارژ';
            case 'phone': return 'تلفنی';
            case 'video': return 'تصویری';
            case 'text': return 'متنی';
            case 'manual': return 'دستی';
            default: return $type ?: 'نامشخص';
        }
    }

    public function formatStatus($status)
    {
        switch ($status) {
            case 'pending': return 'در انتظار';
            case 'paid': return 'پرداخت‌شده';
            case 'failed': return 'ناموفق';
            case 'available': return 'موجود';
            case 'requested': return 'درخواست‌شده';
            case 'unpaid': return 'پرداخت‌نشده';
            case 'scheduled': return 'برنامه‌ریزی‌شده';
            case 'cancelled': return 'لغو شده';
            default: return $status ?: 'نامشخص';
        }
    }

public function render()
{
    $doctorId = Auth::guard('doctor')->user()->id;
    $transactions = $this->readyToLoad ? $this->getTransactions() : collect([]);
    $totalAmount = $this->readyToLoad ? $this->getTotalAmount() : 0;

    // محاسبه جمع مبالغ امروز
    $todayStart = Carbon::today()->startOfDay();
    $todayEnd = Carbon::today()->endOfDay();
    $todayAmount = $this->readyToLoad ? $this->getTotalAmount($todayStart, $todayEnd) : 0;

    $clinics = Clinic::where('doctor_id', $doctorId)->get();

    // گرفتن بیمه‌هایی که در نوبت‌ها برای این دکتر استفاده شدن
    $insurances = Insurance::whereIn('id', function ($query) use ($doctorId) {
        $query->select('insurance_id')
            ->from('appointments')
            ->where('doctor_id', $doctorId)
            ->whereNotNull('insurance_id')
            ->union(
                DB::table('counseling_appointments')
                    ->select('insurance_id')
                    ->where('doctor_id', $doctorId)
                    ->whereNotNull('insurance_id')
            );
    })->get();

    Log::info('Rendering FinancialReport view', [
        'doctor_id' => $doctorId,
        'transaction_count' => $this->readyToLoad ? $transactions->total() : 0,
        'total_amount' => $totalAmount,
        'today_amount' => $todayAmount,
        'start_date' => $this->startDate,
        'end_date' => $this->endDate,
        'insurances_count' => $insurances->count(),
    ]);

    return view('livewire.dr.panel.financial.financial-report', [
        'transactions' => $transactions,
        'totalAmount' => $totalAmount,
        'todayAmount' => $todayAmount, // مقدار جدید
        'clinics' => $clinics,
        'insurances' => $insurances,
        'summary' => $this->summary,
    ]);
}
}
