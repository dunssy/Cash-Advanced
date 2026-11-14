<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class AuditLogController
 *
 * Mengelola penelusuran rekam jejak aktivitas sistem (Audit Trail / Activity Log)
 * pada aplikasi Cash Advance & Settlement PT Technodrive.
 *
 * Setiap aksi penting dalam sistem dicatat secara otomatis dan bersifat kekal (immutable),
 * mencakup: pembuatan pengajuan, persetujuan atasan/direktur, pencairan dana kas,
 * perekaman transaksi struk, pembatalan/void, hingga modifikasi konfigurasi sistem.
 *
 * Sesuai kepatuhan tata kelola (Governance, Risk, and Compliance / GRC), halaman ini
 * hanya dapat diakses oleh Direktur, tim Finance, dan Super Admin.
 *
 * @package App\Http\Controllers
 */
class AuditLogController extends Controller
{
    /**
     * Menampilkan daftar rekam jejak aktivitas (Audit Trail) sistem.
     *
     * Alur kerja:
     * 1. Melakukan pemeriksaan hak akses peran (Otorisasi: Direktur, Finance, atau Super Admin).
     * 2. Menyiapkan kueri data AuditLog beserta informasi relasi user pelaku aktivitas.
     * 3. Menerapkan filter berdasarkan jenis tindakan (action) jika dipilih.
     * 4. Menerapkan filter berdasarkan pengguna pelaku (user_id) jika dipilih.
     * 5. Menerapkan filter pencarian teks bebas (deskripsi kejadian, nama pengguna, IP address).
     * 6. Menjalankan paginasi data serta mengambil daftar kategori aksi unik untuk dropdown filter.
     * 7. Menampilkan data ke view indeks audit trail.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat parameter filter audit trail.
     * @return \Illuminate\View\View
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika peran tidak berwenang.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // 1. Otorisasi Peran: Audit Trail dibatasi hanya untuk Direktur, Finance, dan Super Admin
        // Sesuai dokumen ketentuan sistem hal 8.
        if (!$user->isDirektur() && !$user->isFinance() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk meninjau Audit Trail sistem.');
        }

        // 2. Siapkan kueri log terurut dari rekaman terbaru
        $query = AuditLog::with('user')->orderByDesc('created_at');

        // 3. Filter berdasarkan tipe aksi (misal: LOGIN, CA_APPROVED, EXPENSE_VOIDED, dll)
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 4. Filter berdasarkan ID user pelaku
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 5. Filter pencarian teks bebas pada deskripsi, nama pelaku, atau alamat IP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // 6. Terapkan paginasi 5 data per halaman dengan menjaga parameter query URL
        $logs = $query->paginate(5)->withQueryString();

        // 7. Ambil daftar variasi jenis aksi unik untuk opsi filter dropdown antarmuka
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        // 8. Tampilkan data ke view audit log
        return view('audit_logs.index', compact('logs', 'actions'));
    }
}
