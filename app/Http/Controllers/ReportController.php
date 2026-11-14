<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ReportController
 *
 * Mengelola penyusunan dan ekspor 4 modul laporan utama pada sistem
 * Cash Advance & Settlement PT Technodrive (Dokumen Ketentuan Sistem hal 8):
 *
 * 1. Laporan Rincian per CA (Detailed CA Report):
 *    Menyajikan data komprehensif satu Cash Advance tertentu, rincian seluruh struk,
 *    riwayat top-up, status verifikasi, sisa saldo kas, serta ekspor CSV/Cetak PDF.
 * 2. Laporan Rekapitulasi Bulanan (Monthly Summary Report):
 *    Menyajikan total dana CA cair, top-up berjalan, breakdown pengeluaran valid per kategori
 *    (material, transport, sewa alat, konsumsi, lainnya), serta total dana refund kembali ke kas.
 * 3. Laporan CA Menggantung / Outstanding (Outstanding & Overdue Report):
 *    Memantau CA yang belum ditutup (belum CLOSED), hitung mundur sisa hari SLA durasi maksimal,
 *    dan peringatan status jatuh tempo (overdue).
 * 4. Laporan Rekapitulasi Kinerja per Karyawan (Employee Performance & Compliance Report):
 *    Memantau kepatuhan operasional staf: jumlah CA aktif, keterlambatan pencatatan struk (> H+1),
 *    keterlambatan penyetoran refund (> 3 hari kerja), dan CA yang mengalami overdue.
 *
 * @package App\Http\Controllers
 */
class ReportController extends Controller
{
    /**
     * Menampilkan Laporan Rincian per Dokumen Cash Advance.
     *
     * Alur kerja:
     * 1. Menentukan daftar CA yang dapat diakses pengguna berdasarkan hierarki peran:
     *    - Karyawan: Hanya CA miliknya sendiri.
     *    - Atasan: CA miliknya dan bawahan langsungnya.
     *    - Finance/Direktur/Super Admin: Seluruh CA perusahaan.
     * 2. Mengambil data CA terpilih beserta rincian struk, lampiran fisik, dan riwayat top-up.
     * 3. Menangani ekspor berkas format CSV jika parameter `export=csv` terdeteksi.
     * 4. Mendukung mode cetak ramah printer (print-friendly) jika `print=true`.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat filter ca_id, format ekspor, atau mode cetak.
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function rincianCA(Request $request): View|StreamedResponse
    {
        $user = Auth::user();

        // 1. Inisialisasi kueri daftar CA terurut dari yang terbaru
        $caQuery = CashAdvance::with(['user', 'expenses.attachments', 'topUps'])->orderByDesc('created_at');

        // 2. Pembatasan cakupan data sesuai peran pengguna
        if ($user->isKaryawan()) {
            $caQuery->where('user_id', $user->id);
        } elseif ($user->isAtasan()) {
            $subordinateIds = $user->subordinates()->pluck('id')->toArray();
            $caQuery->where(function ($q) use ($user, $subordinateIds) {
                $q->where('user_id', $user->id)->orWhereIn('user_id', $subordinateIds);
            });
        }

        $allCAs = (clone $caQuery)->get();

        // 3. Tentukan CA yang sedang dipilih (default: CA terbaru)
        $selectedId = $request->input('ca_id', $allCAs->first()?->id);
        $selectedCA = $selectedId 
            ? CashAdvance::with(['user.manager', 'approver', 'disburser', 'expenses.attachments', 'expenses.verifier', 'topUps'])->find($selectedId) 
            : null;

        // 4. Tangani ekspor data ke format CSV
        if ($request->input('export') === 'csv' && $selectedCA) {
            return $this->exportRincianCsv($selectedCA);
        }

        // 5. Cek apakah tampilan dialihkan ke mode cetak kertas/PDF
        $isPrint = $request->boolean('print');

        // 6. Tampilkan antarmuka laporan rincian CA
        return view('reports.rincian_ca', compact('allCAs', 'selectedCA', 'isPrint'));
    }

    /**
     * Menampilkan Laporan Rekapitulasi Keuangan Bulanan.
     *
     * Alur kerja:
     * 1. Otorisasi peran: Terbatas hanya untuk tim Finance, Direktur, dan Super Admin.
     * 2. Mengambil data CA pada bulan dan tahun yang dipilih (default: bulan & tahun berjalan).
     * 3. Menghitung total dana awal CA disetujui, total top-up cair, dan akumulasi dana operasional.
     * 4. Mengelompokkan pengeluaran yang berstatus VALID ke dalam 5 kategori akun biaya:
     *    material, transport, sewa alat, konsumsi, dan lainnya (mengecualikan transaksi talangan).
     * 5. Menghitung total sisa dana kas yang telah disetorkan kembali (refund).
     * 6. Menangani ekspor format CSV jika parameter `export=csv` terdeteksi.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat parameter bulan, tahun, atau ekspor.
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika peran tidak berwenang.
     */
    public function rekapBulanan(Request $request): View|StreamedResponse
    {
        $user = Auth::user();

        // 1. Otorisasi Peran: Laporan Rekap Bulanan hanya untuk Finance, Direktur, dan Super Admin
        if (!$user->isFinance() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang membuka laporan rekap bulanan.');
        }

        // 2. Ambil parameter periode bulan dan tahun
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        // 3. Ambil seluruh CA yang diterbitkan pada periode tersebut
        $cas = CashAdvance::with(['expenses', 'topUps'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();

        // 4. Kalkulasi total dana kas awal dan penambahan top-up
        $totalCAInitial = $cas->sum(fn($c) => $c->approved_amount ?: $c->requested_amount);
        $totalTopup = $cas->sum(fn($c) => $c->disbursed_topups_total);
        $totalDana = $totalCAInitial + $totalTopup;

        // 5. Inisialisasi struktur breakdown biaya per kategori akun
        $categories = [
            'material' => 0,
            'transport' => 0,
            'sewa_alat' => 0,
            'konsumsi' => 0,
            'lainnya' => 0,
        ];

        // 6. Akumulasi pengeluaran berstatus VALID (tidak termasuk talangan pribadi)
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

        // 7. Hitung total dana sisa yang dikembalikan ke rekening perusahaan (Refund)
        $totalRefund = $cas->sum('settlement_refund_amount');

        // 8. Tangani ekspor CSV jika diminta
        if ($request->input('export') === 'csv') {
            return $this->exportRekapBulananCsv($month, $year, $cas, $totalCAInitial, $totalTopup, $categories, $totalValidExpense, $totalRefund);
        }

        $isPrint = $request->boolean('print');

        // 9. Tampilkan ke view rekap bulanan
        return view('reports.rekap_bulanan', compact(
            'month',
            'year',
            'cas',
            'totalCAInitial',
            'totalTopup',
            'totalDana',
            'categories',
            'totalValidExpense',
            'totalRefund',
            'isPrint'
        ));
    }

    /**
     * Menampilkan Laporan Cash Advance Menggantung (Outstanding & Overdue).
     *
     * Berfungsi sebagai alat pengawasan tim Finance dan Direktur untuk mengidentifikasi
     * seluruh CA yang belum berstatus 'CLOSED' atau 'REJECTED', memantau hitung mundur
     * sisa hari pelaksanaan proyek, serta mendeteksi keterlambatan jatuh tempo (Overdue).
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request.
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika peran tidak berwenang.
     */
    public function outstanding(Request $request): View|StreamedResponse
    {
        $user = Auth::user();

        // 1. Otorisasi peran manajerial & fiskal
        if (!$user->isFinance() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang membuka laporan outstanding.');
        }

        // 2. Ambil seluruh CA yang masih menggantung / aktif berjalan
        $outstandingCAs = CashAdvance::with(['user', 'approver', 'disburser', 'expenses', 'topUps'])
            ->whereNotIn('status', ['CLOSED', 'REJECTED'])
            ->orderBy('due_date')
            ->get();

        // 3. Tangani ekspor berkas format CSV jika diminta
        if ($request->input('export') === 'csv') {
            return $this->exportOutstandingCsv($outstandingCAs);
        }

        $isPrint = $request->boolean('print');

        // 4. Tampilkan ke view laporan outstanding
        return view('reports.outstanding', compact('outstandingCAs', 'isPrint'));
    }

    /**
     * Menampilkan Laporan Rekapitulasi Kepatuhan & Performa Karyawan.
     *
     * Metrik evaluasi kepatuhan staf (SOP PT Technodrive):
     * 1. Total kuantitas dan nominal CA yang pernah diajukan.
     * 2. Jumlah struk yang terlambat dicatat (> H+1 dari tanggal kejadian transaksi).
     * 3. Jumlah penyetoran refund yang terlambat disetor (> 3 hari kerja setelah status NEED_SETTLEMENT).
     * 4. Jumlah CA yang saat ini masih aktif dan CA yang mengalami keterlambatan (Overdue).
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request.
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika peran tidak berwenang.
     */
    public function rekapKaryawan(Request $request): View|StreamedResponse
    {
        $user = Auth::user();

        // 1. Otorisasi peran
        if (!$user->isFinance() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang membuka laporan kinerja karyawan.');
        }

        // 2. Ambil data seluruh karyawan dan petakan metrik kepatuhan
        $employees = User::where('role', '!=', 'super_admin')
            ->with(['cashAdvances.expenses'])
            ->get()
            ->map(function ($emp) {
                $cas = $emp->cashAdvances;
                $totalCA = $cas->count();
                $totalNominal = $cas->sum(fn($c) => $c->approved_amount ?: $c->requested_amount);
                
                // Menghitung frekuensi keterlambatan pencatatan pengeluaran (> H+1)
                $lateExpenseCount = 0;
                foreach ($cas as $c) {
                    $lateExpenseCount += $c->expenses->where('is_late', true)->count();
                }

                // Menghitung frekuensi keterlambatan refund (> 3 hari kerja setelah status NEED_SETTLEMENT)
                $lateRefundCount = $cas->filter(function ($c) {
                    if ($c->settlement_transferred_at && $c->project_completed_at) {
                        return $c->project_completed_at->diffInDays($c->settlement_transferred_at) > 3;
                    }
                    return false;
                })->count();

                // Jumlah CA aktif saat ini
                $activeCount = $cas->whereIn('status', ['ACTIVE', 'NEED_SETTLEMENT', 'PENDING_VERIFICATION'])->count();
                
                // Jumlah CA yang berstatus jatuh tempo (Overdue)
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

        // 3. Tangani ekspor CSV jika diminta
        if ($request->input('export') === 'csv') {
            return $this->exportKaryawanCsv($employees);
        }

        $isPrint = $request->boolean('print');

        // 4. Render ke antarmuka rekap performa karyawan
        return view('reports.rekap_karyawan', compact('employees', 'isPrint'));
    }

    /**
     * Menghasilkan streaming file CSV untuk Laporan Rincian Cash Advance.
     *
     * @param  \App\Models\CashAdvance  $ca Model Cash Advance yang diekspor.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportRincianCsv(CashAdvance $ca): StreamedResponse
    {
        $fileName = "Rincian_{$ca->ca_number}.csv";

        return response()->streamDownload(function () use ($ca) {
            $handle = fopen('php://output', 'w');
            
            // Header Informasi Dokumen
            fputcsv($handle, ['LAPORAN RINCIAN CASH ADVANCE - PT TECHNODRIVE']);
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
            
            // Header Tabel Rincian Struk
            fputcsv($handle, ['DAFTAR PENGELUARAN']);
            fputcsv($handle, ['No', 'Tanggal', 'Keterangan', 'Kategori', 'Vendor', 'Nominal', 'Tipe', 'Terlambat', 'Status Verifikasi', 'Catatan']);

            // Baris Data Pengeluaran
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

    /**
     * Menghasilkan streaming file CSV untuk Laporan Rekapitulasi Bulanan.
     *
     * @param  int|string  $month Bulan laporan.
     * @param  int|string  $year Tahun laporan.
     * @param  \Illuminate\Support\Collection  $cas Koleksi data CA pada periode terkait.
     * @param  float  $totalCAInitial Total dana awal CA.
     * @param  float  $totalTopup Total dana top-up.
     * @param  array  $categories Array ringkasan biaya per kategori.
     * @param  float  $totalValidExpense Total pengeluaran valid.
     * @param  float  $totalRefund Total pengembalian kas.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportRekapBulananCsv($month, $year, $cas, $totalCAInitial, $totalTopup, $categories, $totalValidExpense, $totalRefund): StreamedResponse
    {
        $fileName = "Rekap_Bulanan_{$year}_{$month}.csv";

        return response()->streamDownload(function () use ($month, $year, $cas, $totalCAInitial, $totalTopup, $categories, $totalValidExpense, $totalRefund) {
            $handle = fopen('php://output', 'w');
            
            // Header Ringkasan Keuangan
            fputcsv($handle, ["REKAP BULANAN CASH ADVANCE - PT TECHNODRIVE ({$month}/{$year})"]);
            fputcsv($handle, ['Total CA Disetujui', $totalCAInitial]);
            fputcsv($handle, ['Total Top-Up Cair', $totalTopup]);
            fputcsv($handle, ['Total Dana Dikeluarkan', $totalCAInitial + $totalTopup]);
            fputcsv($handle, ['Total Pengeluaran Valid', $totalValidExpense]);
            fputcsv($handle, ['Total Sisa Refund Kembali', $totalRefund]);
            fputcsv($handle, []);
            
            // Rincian Akun Biaya
            fputcsv($handle, ['RINCIAN PENGELUARAN VALID PER KATEGORI']);
            fputcsv($handle, ['Material & Perlengkapan', $categories['material']]);
            fputcsv($handle, ['Transportasi & Bensin', $categories['transport']]);
            fputcsv($handle, ['Sewa Alat & Kendaraan', $categories['sewa_alat']]);
            fputcsv($handle, ['Konsumsi & Makan', $categories['konsumsi']]);
            fputcsv($handle, ['Lain-lain', $categories['lainnya']]);
            fputcsv($handle, []);
            
            // Daftar Rincian Dokumen CA
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

    /**
     * Menghasilkan streaming file CSV untuk Laporan CA Menggantung (Outstanding).
     *
     * @param  \Illuminate\Support\Collection  $cas Koleksi data CA yang berstatus outstanding.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportOutstandingCsv($cas): StreamedResponse
    {
        $fileName = "CA_Menggantung_" . date('Ymd') . ".csv";

        return response()->streamDownload(function () use ($cas) {
            $handle = fopen('php://output', 'w');
            
            // Header Laporan
            fputcsv($handle, ['LAPORAN CASH ADVANCE MENGGANTUNG (OUTSTANDING) - PT TECHNODRIVE']);
            fputcsv($handle, ['Nomor CA', 'Karyawan', 'Proyek', 'Status', 'Tanggal Cair', 'Jatuh Tempo', 'Sisa Hari', 'Overdue?', 'Saldo Awal', 'Sisa Saldo']);

            // Baris Data
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

    /**
     * Menghasilkan streaming file CSV untuk Laporan Rekapitulasi Performa & Kepatuhan Karyawan.
     *
     * @param  \Illuminate\Support\Collection  $employees Koleksi metrik kinerja staf.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportKaryawanCsv($employees): StreamedResponse
    {
        $fileName = "Rekap_Karyawan_" . date('Ymd') . ".csv";

        return response()->streamDownload(function () use ($employees) {
            $handle = fopen('php://output', 'w');
            
            // Header Laporan
            fputcsv($handle, ['REKAP PERFORMA KARYAWAN CASH ADVANCE - PT TECHNODRIVE']);
            fputcsv($handle, ['Nama Karyawan', 'Email', 'Departemen', 'Total CA', 'Total Nominal CA', 'Jumlah Telat Catat Struk', 'Jumlah Telat Refund', 'CA Aktif', 'CA Overdue']);

            // Baris Data Performa Karyawan
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
