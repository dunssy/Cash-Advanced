<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    // 1. Laporan Rincian per CA
    public function rincianCA(Request $request)
    {
        $user = Auth::user();
        $caQuery = CashAdvance::with(['user', 'expenses.attachments', 'topUps'])->orderByDesc('created_at');

        if ($user->isKaryawan()) {
            $caQuery->where('user_id', $user->id);
        } elseif ($user->isAtasan()) {
            $subordinateIds = $user->subordinates()->pluck('id')->toArray();
            $caQuery->where(function ($q) use ($user, $subordinateIds) {
                $q->where('user_id', $user->id)->orWhereIn('user_id', $subordinateIds);
            });
        }

        $allCAs = (clone $caQuery)->get();
        $selectedId = $request->input('ca_id', $allCAs->first()?->id);
        $selectedCA = $selectedId ? CashAdvance::with(['user.manager', 'approver', 'disburser', 'expenses.attachments', 'expenses.verifier', 'topUps'])->find($selectedId) : null;

        if ($request->input('export') === 'csv' && $selectedCA) {
            return $this->exportRincianCsv($selectedCA);
        }

        $isPrint = $request->boolean('print');

        return view('reports.rincian_ca', compact('allCAs', 'selectedCA', 'isPrint'));
    }

    // 2. Laporan Rekap Bulanan
    public function rekapBulanan(Request $request)
    {
        $user = Auth::user();
        if (!$user->isFinance() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $cas = CashAdvance::with(['expenses', 'topUps'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        $totalCAInitial = $cas->sum(fn($c) => $c->approved_amount ?: $c->requested_amount);
        $totalTopup = $cas->sum(fn($c) => $c->disbursed_topups_total);
        $totalDana = $totalCAInitial + $totalTopup;

        // Categories breakdown for valid expenses
        $categories = [
            'material' => 0,
            'transport' => 0,
            'sewa_alat' => 0,
            'konsumsi' => 0,
            'lainnya' => 0,
        ];

        $totalValidExpense = 0;
        foreach ($cas as $c) {
            foreach ($c->expenses as $exp) {
                if ($exp->verification_status === 'VALID' && !$exp->is_talangan) {
                    $cat = $exp->category;
                    if (isset($categories[$cat])) {
                        $categories[$cat] += (float) $exp->amount;
                    } else {
                        $categories['lainnya'] += (float) $exp->amount;
                    }
                    $totalValidExpense += (float) $exp->amount;
                }
            }
        }

        $totalRefund = $cas->sum('settlement_refund_amount');

        if ($request->input('export') === 'csv') {
            return $this->exportRekapBulananCsv($month, $year, $cas, $totalCAInitial, $totalTopup, $categories, $totalValidExpense, $totalRefund);
        }

        $isPrint = $request->boolean('print');

        return view('reports.rekap_bulanan', compact('month', 'year', 'cas', 'totalCAInitial', 'totalTopup', 'totalDana', 'categories', 'totalValidExpense', 'totalRefund', 'isPrint'));
    }

    // 3. Laporan CA Menggantung (Outstanding)
    public function outstanding(Request $request)
    {
        $user = Auth::user();
        if (!$user->isFinance() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $outstandingCAs = CashAdvance::with(['user', 'approver', 'disburser', 'expenses', 'topUps'])
            ->whereNotIn('status', ['CLOSED', 'REJECTED'])
            ->orderBy('due_date')
            ->get();

        if ($request->input('export') === 'csv') {
            return $this->exportOutstandingCsv($outstandingCAs);
        }

        $isPrint = $request->boolean('print');

        return view('reports.outstanding', compact('outstandingCAs', 'isPrint'));
    }

    // 4. Laporan Rekap per Karyawan
    public function rekapKaryawan(Request $request)
    {
        $user = Auth::user();
        if (!$user->isFinance() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $employees = User::where('role', '!=', 'super_admin')
            ->with(['cashAdvances.expenses'])
            ->get()
            ->map(function ($emp) {
                $cas = $emp->cashAdvances;
                $totalCA = $cas->count();
                $totalNominal = $cas->sum(fn($c) => $c->approved_amount ?: $c->requested_amount);
                
                $lateExpenseCount = 0;
                foreach ($cas as $c) {
                    $lateExpenseCount += $c->expenses->where('is_late', true)->count();
                }

                // Late refund: if settlement transfer happened after 3 work days from status change
                $lateRefundCount = $cas->filter(function ($c) {
                    if ($c->settlement_transferred_at && $c->project_completed_at) {
                        return $c->project_completed_at->diffInDays($c->settlement_transferred_at) > 3;
                    }
                    return false;
                })->count();

                $activeCount = $cas->whereIn('status', ['ACTIVE', 'NEED_SETTLEMENT', 'PENDING_VERIFICATION'])->count();
                $overdueCount = $cas->filter->is_overdue->count();

                return [
                    'user' => $emp,
                    'total_ca' => $totalCA,
                    'total_nominal' => $totalNominal,
                    'late_expense_count' => $lateExpenseCount,
                    'late_refund_count' => $lateRefundCount,
                    'active_count' => $activeCount,
                    'overdue_count' => $overdueCount,
                ];
            });

        if ($request->input('export') === 'csv') {
            return $this->exportKaryawanCsv($employees);
        }

        $isPrint = $request->boolean('print');

        return view('reports.rekap_karyawan', compact('employees', 'isPrint'));
    }

    // CSV Exporters
    private function exportRincianCsv(CashAdvance $ca): StreamedResponse
    {
        $fileName = "Rincian_{$ca->ca_number}.csv";

        return response()->streamDownload(function () use ($ca) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['LAPORAN RINCIAN CASH ADVANCE - PT TECHNODRIVES']);
            fputcsv($handle, ['Nomor CA', $ca->ca_number]);
            fputcsv($handle, ['Karyawan', $ca->user->name]);
            fputcsv($handle, ['Proyek', $ca->project_name . ' (' . ($ca->project_code ?: '-') . ')']);
            fputcsv($handle, ['Status', $ca->status]);
            fputcsv($handle, ['CA Awal Disetujui', $ca->approved_amount]);
            fputcsv($handle, ['Total Top-Up Cair', $ca->disbursed_topups_total]);
            fputcsv($handle, ['Total Pengeluaran Tercatat', $ca->recorded_expenses_total]);
            fputcsv($handle, ['Sisa Saldo', $ca->current_balance]);
            fputcsv($handle, ['Talangan Disetujui', $ca->approved_talangan_total]);
            fputcsv($handle, ['Hasil Settlement', $ca->settlement_result]);
            fputcsv($handle, []);
            fputcsv($handle, ['DAFTAR PENGELUARAN']);
            fputcsv($handle, ['No', 'Tanggal', 'Keterangan', 'Kategori', 'Vendor', 'Nominal', 'Tipe', 'Terlambat', 'Status Verifikasi', 'Catatan']);

            foreach ($ca->expenses as $idx => $exp) {
                fputcsv($handle, [
                    $idx + 1,
                    $exp->transaction_date->format('d/m/Y'),
                    $exp->description,
                    $exp->category_label,
                    $exp->vendor_name,
                    $exp->amount,
                    $exp->is_talangan ? 'Talangan' : 'Normal',
                    $exp->is_late ? 'YA (Terlambat)' : 'Tepat Waktu',
                    $exp->verification_status,
                    $exp->verification_notes ?: '-',
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function exportRekapBulananCsv($month, $year, $cas, $totalCAInitial, $totalTopup, $categories, $totalValidExpense, $totalRefund): StreamedResponse
    {
        $fileName = "Rekap_Bulanan_{$year}_{$month}.csv";

        return response()->streamDownload(function () use ($month, $year, $cas, $totalCAInitial, $totalTopup, $categories, $totalValidExpense, $totalRefund) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ["REKAP BULANAN CASH ADVANCE - PT TECHNODRIVES ({$month}/{$year})"]);
            fputcsv($handle, ['Total CA Disetujui', $totalCAInitial]);
            fputcsv($handle, ['Total Top-Up Cair', $totalTopup]);
            fputcsv($handle, ['Total Dana Dikeluarkan', $totalCAInitial + $totalTopup]);
            fputcsv($handle, ['Total Pengeluaran Valid', $totalValidExpense]);
            fputcsv($handle, ['Total Sisa Refund Kembali', $totalRefund]);
            fputcsv($handle, []);
            fputcsv($handle, ['RINCIAN PENGELUARAN VALID PER KATEGORI']);
            fputcsv($handle, ['Material & Perlengkapan', $categories['material']]);
            fputcsv($handle, ['Transportasi & Bensin', $categories['transport']]);
            fputcsv($handle, ['Sewa Alat & Kendaraan', $categories['sewa_alat']]);
            fputcsv($handle, ['Konsumsi & Makan', $categories['konsumsi']]);
            fputcsv($handle, ['Lain-lain', $categories['lainnya']]);
            fputcsv($handle, []);
            fputcsv($handle, ['DAFTAR CA PADA BULAN INI']);
            fputcsv($handle, ['Nomor CA', 'Karyawan', 'Proyek', 'Status', 'Dana CA', 'Topup', 'Pengeluaran Valid', 'Refund']);

            foreach ($cas as $c) {
                fputcsv($handle, [
                    $c->ca_number,
                    $c->user->name,
                    $c->project_name,
                    $c->status,
                    $c->approved_amount ?: $c->requested_amount,
                    $c->disbursed_topups_total,
                    $c->valid_expenses_total,
                    $c->settlement_refund_amount,
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function exportOutstandingCsv($cas): StreamedResponse
    {
        $fileName = "CA_Menggantung_" . date('Ymd') . ".csv";

        return response()->streamDownload(function () use ($cas) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['LAPORAN CASH ADVANCE MENGGANTUNG (OUTSTANDING) - PT TECHNODRIVES']);
            fputcsv($handle, ['Nomor CA', 'Karyawan', 'Proyek', 'Status', 'Tanggal Cair', 'Jatuh Tempo', 'Sisa Hari', 'Overdue?', 'Saldo Awal', 'Sisa Saldo']);

            foreach ($cas as $c) {
                fputcsv($handle, [
                    $c->ca_number,
                    $c->user->name,
                    $c->project_name,
                    $c->status,
                    $c->disbursed_at ? $c->disbursed_at->format('d/m/Y') : '-',
                    $c->due_date ? $c->due_date->format('d/m/Y') : '-',
                    $c->days_remaining,
                    $c->is_overdue ? 'YA (OVERDUE)' : 'TIDAK',
                    $c->approved_amount ?: $c->requested_amount,
                    $c->current_balance,
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function exportKaryawanCsv($employees): StreamedResponse
    {
        $fileName = "Rekap_Karyawan_" . date('Ymd') . ".csv";

        return response()->streamDownload(function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['REKAP PERFORMA KARYAWAN CASH ADVANCE - PT TECHNODRIVES']);
            fputcsv($handle, ['Nama Karyawan', 'Email', 'Departemen', 'Total CA', 'Total Nominal CA', 'Jumlah Telat Catat Struk', 'Jumlah Telat Refund', 'CA Aktif', 'CA Overdue']);

            foreach ($employees as $row) {
                fputcsv($handle, [
                    $row['user']->name,
                    $row['user']->email,
                    $row['user']->department ?: '-',
                    $row['total_ca'],
                    $row['total_nominal'],
                    $row['late_expense_count'],
                    $row['late_refund_count'],
                    $row['active_count'],
                    $row['overdue_count'],
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
