<?php

namespace App\Http\Controllers\Dr\Panel\FinancialReport;

use App\Http\Controllers\Dr\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

class FinancialReportController extends Controller
{
    public function index()
    {
        Log::info('Financial Report page accessed by doctor', [
            'doctor_id' => Auth::guard('doctor')->user()->id
        ]);
        return view('dr.panel.financial-reports.index');
    }

    public function exportExcel()
    {
        Log::info('Export Excel requested', [
            'doctor_id' => Auth::guard('doctor')->user()->id
        ]);
        return Excel::download(
            new FinancialReportExport(session('financial_report_filters', [])),
            'financial-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        Log::info('Export PDF requested', [
            'doctor_id' => Auth::guard('doctor')->user()->id
        ]);
        $transactions = session('financial_report_data', []);

        if (empty($transactions)) {
            Log::warning('No transactions found for PDF export', [
                'doctor_id' => Auth::guard('doctor')->user()->id
            ]);
            return redirect()->back()->with('error', 'هیچ داده‌ای برای خروجی PDF یافت نشد.');
        }

        $pdf = Pdf::loadView('dr.panel.financial-reports.pdf', compact('transactions'));
        return $pdf->download('financial-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
