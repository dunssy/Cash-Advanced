<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\ProjectCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectCodeController extends Controller
{
    /**
     * Menampilkan daftar Kode Proyek resmi yang dapat dikelola oleh Atasan / Manajemen.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Otorisasi peran: Hanya Atasan, Direktur, dan Super Admin yang berwenang mengelola kode proyek resmi
        if (!$user->isAtasan() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak: Hanya Atasan atau Manajemen yang dapat mengelola master kode proyek.');
        }

        $query = ProjectCode::with('creator')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $projectCodes = $query->paginate(15)->withQueryString();

        // Standard divisions list
        $divisions = [
            'Engineering & Commissioning',
            'Instalasi Lapangan',
            'Operasional & Maintenance',
            'Field Service',
            'Perencanaan Proyek',
            'Finance & Accounting',
            'IT & Systems',
        ];

        return view('project_codes.index', compact('projectCodes', 'divisions'));
    }

    /**
     * Menyimpan Kode Proyek baru yang dibuat oleh Atasan.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAtasan() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:project_codes,code'],
            'description' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:100'],
        ], [
            'code.required' => 'Kode proyek wajib diisi.',
            'code.unique' => 'Kode proyek ini sudah terdaftar sebelumnya.',
        ]);

        $projectCode = ProjectCode::create([
            'code' => strtoupper(trim($validated['code'])),
            'description' => $validated['description'] ?? null,
            'department' => $validated['department'] ?? ($user->departments[0] ?? null),
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        AuditLog::record(
            'PROJECT_CODE_CREATED',
            $projectCode,
            "Kode Proyek {$projectCode->code} berhasil dibuat oleh {$user->name} ({$user->role})."
        );

        return redirect()->route('project-codes.index')
            ->with('success', "Kode Proyek {$projectCode->code} berhasil ditambahkan dan siap digunakan pada pengajuan CA.");
    }

    /**
     * Mengubah status aktif / nonaktif kode proyek.
     */
    public function toggleStatus(ProjectCode $projectCode): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAtasan() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $projectCode->update([
            'is_active' => !$projectCode->is_active,
        ]);

        $statusText = $projectCode->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->route('project-codes.index')
            ->with('success', "Kode Proyek {$projectCode->code} berhasil {$statusText}.");
    }

    /**
     * Menghapus kode proyek jika belum pernah dipakai di Cash Advance.
     */
    public function destroy(ProjectCode $projectCode): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAtasan() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        // Cek apakah kode proyek sudah terikat pada CA
        $isUsed = CashAdvance::where('project_code', $projectCode->code)->exists();
        if ($isUsed) {
            return back()->with('error', "Kode Proyek {$projectCode->code} tidak dapat dihapus karena sudah tercatat dalam riwayat Cash Advance. Anda dapat menonaktifkannya.");
        }

        $codeName = $projectCode->code;
        $projectCode->delete();

        return redirect()->route('project-codes.index')
            ->with('success', "Kode Proyek {$codeName} berhasil dihapus.");
    }
}
