<?php

namespace App\Http\Controllers\Mc\Panel\FinancialReport;

use App\Http\Controllers\Mc\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

class FinancialReportController extends Controller
{
    public function index()
    {

        return view('mc.panel.financial-reports.index');
    }

    public function exportExcel()
    {

        return Excel::download(
            new FinancialReportExport(session('financial_report_filters', [])),
            'financial-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPdf()
    {

        $transactions = session('financial_report_data', []);

        if (empty($transactions)) {

            return redirect()->back()->with('error', 'هیچ داده‌ای برای خروجی PDF یافت نشد.');
        }

        $pdf = Pdf::loadView('mc.panel.financial-reports.pdf', compact('transactions'));
        return $pdf->download('financial-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
