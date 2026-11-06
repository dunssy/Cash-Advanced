<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\ExpenseAttachment;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\TopUp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed System Settings
        $settings = [
            [
                'key' => 'max_ca_duration_days',
                'value' => '60',
                'label' => 'Durasi Maksimal CA (Hari)',
                'type' => 'number',
                'description' => 'Durasi maksimal satu CA dihitung sejak dana dicairkan (standar: 2 bulan / 60 hari)',
            ],
            [
                'key' => 'topup_nominal_threshold',
                'value' => '5000000',
                'label' => 'Batas Nominal Top-Up Butuh Direktur (Rp)',
                'type' => 'number',
                'description' => 'Top-up dengan nominal di atas batas ini wajib disetujui Direktur',
            ],
            [
                'key' => 'topup_percent_threshold',
                'value' => '50',
                'label' => 'Batas Persentase Top-Up Butuh Direktur (%)',
                'type' => 'number',
                'description' => 'Top-up dengan nilai di atas sekian persen dari CA awal wajib disetujui Direktur',
            ],
            [
                'key' => 'topup_max_times_before_direktur',
                'value' => '2',
                'label' => 'Batas Frekuensi Top-Up Sebelum Direktur',
                'type' => 'number',
                'description' => 'Pengajuan top-up ke-3 dan seterusnya otomatis naik ke Direktur',
            ],
            [
                'key' => 'low_balance_warning_percent',
                'value' => '20',
                'label' => 'Ambang Peringatan Saldo Rendah (%)',
                'type' => 'number',
                'description' => 'Sistem mengirim peringatan saat sisa saldo kurang dari persentase ini',
            ],
            [
                'key' => 'settlement_deadline_days',
                'value' => '3',
                'label' => 'Batas Waktu Pengembalian Sisa (Hari Kerja)',
                'type' => 'number',
                'description' => 'Batas hari kerja untuk transfer sisa dana dan upload bukti refund setelah status NEED_SETTLEMENT',
            ],
            [
                'key' => 'late_expense_threshold_days',
                'value' => '1',
                'label' => 'Batas Keterlambatan Catat Struk (Hari)',
                'type' => 'number',
                'description' => 'Pencatatan lebih dari 1 hari setelah tanggal transaksi diberi label TERLAMBAT',
            ],
            [
                'key' => 'company_bank_name',
                'value' => 'Bank Central Asia (BCA)',
                'label' => 'Nama Bank Kantor untuk Refund',
                'type' => 'string',
                'description' => 'Rekening tujuan pengembalian sisa dana CA',
            ],
            [
                'key' => 'company_bank_account',
                'value' => '712-888-9900',
                'label' => 'Nomor Rekening Kantor',
                'type' => 'string',
                'description' => 'Nomor rekening kantor PT Technodrives',
            ],
            [
                'key' => 'company_bank_holder',
                'value' => 'PT TECHNODRIVES INDONESIA',
                'label' => 'Nama Pemilik Rekening Kantor',
                'type' => 'string',
                'description' => 'Atas nama rekening kantor',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 2. Seed Users for All 5 Roles
        $commonPassword = Hash::make('password');

        $direktur = User::create([
            'name' => 'Bambang Soedirgo',
            'email' => 'direktur@technodrives.co.id',
            'password' => $commonPassword,
            'role' => 'direktur',
            'department' => 'Direksi',
            'phone' => '081122334455',
            'bank_name' => 'BCA',
            'bank_account_number' => '1112223334',
            'bank_account_holder' => 'Bambang Soedirgo',
        ]);

        $superAdmin = User::create([
            'name' => 'Super Admin IT',
            'email' => 'superadmin@technodrives.co.id',
            'password' => $commonPassword,
            'role' => 'super_admin',
            'department' => 'IT & Systems',
            'phone' => '081234567890',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '9988776655',
            'bank_account_holder' => 'Super Admin IT',
        ]);

        $finance = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'finance@technodrives.co.id',
            'password' => $commonPassword,
            'role' => 'finance',
            'manager_id' => $direktur->id,
            'department' => 'Finance & Accounting',
            'phone' => '081399887766',
            'bank_name' => 'BCA',
            'bank_account_number' => '5544332211',
            'bank_account_holder' => 'Dewi Lestari',
        ]);

        $atasan = User::create([
            'name' => 'Indri Handayani',
            'email' => 'atasan@technodrives.co.id',
            'password' => $commonPassword,
            'role' => 'atasan',
            'manager_id' => $direktur->id,
            'department' => 'Operasional & Engineering',
            'phone' => '081288776655',
            'bank_name' => 'BCA',
            'bank_account_number' => '3322114455',
            'bank_account_holder' => 'Indri Handayani',
        ]);

        $karyawan = User::create([
            'name' => 'Ahmad Maulidun',
            'email' => 'karyawan@technodrives.co.id',
            'password' => $commonPassword,
            'role' => 'karyawan',
            'manager_id' => $atasan->id,
            'department' => 'Engineering & Commissioning',
            'phone' => '081577665544',
            'bank_name' => 'BCA',
            'bank_account_number' => '7788990011',
            'bank_account_holder' => 'Ahmad Maulidun',
        ]);

        $budi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@technodrives.co.id',
            'password' => $commonPassword,
            'role' => 'karyawan',
            'manager_id' => $atasan->id,
            'department' => 'Field Service',
            'phone' => '081622334455',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '8899001122',
            'bank_account_holder' => 'Budi Santoso',
        ]);

        // 3. Seed Sample CAs
        // Sample CA 1: ACTIVE (Ahmad Maulidun - Proyek Petrokimia Gresik)
        $caActive = CashAdvance::create([
            'ca_number' => 'CA-202611-0001',
            'user_id' => $karyawan->id,
            'project_name' => 'Instalasi Drive Inverter PT Petrokimia Gresik',
            'project_code' => 'PRJ-2026-PKG01',
            'purpose' => 'Biaya akomodasi, sewa scaffolding, transport tim 3 orang selama komisioning drive 250kW di Gresik',
            'requested_amount' => 10000000,
            'approved_amount' => 10000000,
            'status' => 'ACTIVE',
            'approved_by' => $atasan->id,
            'approved_at' => Carbon::now()->subDays(10),
            'approval_notes' => 'Disetujui sesuai rencana anggaran proyek.',
            'disbursed_by' => $finance->id,
            'disbursed_at' => Carbon::now()->subDays(9),
            'disbursement_ref' => 'TRF-BCA-889912',
            'disbursement_notes' => 'Sudah ditransfer ke rekening BCA Ahmad Maulidun',
            'due_date' => Carbon::now()->addDays(51), // Total 60 days
        ]);

        // Expense 1 for CA 1: Transport & Bensin
        $exp1 = Expense::create([
            'cash_advance_id' => $caActive->id,
            'user_id' => $karyawan->id,
            'transaction_date' => Carbon::now()->subDays(8)->format('Y-m-d'),
            'description' => 'Bensin & Tol Mobil Operasional Surabaya - Gresik',
            'category' => 'transport',
            'amount' => 750000,
            'vendor_name' => 'SPBU Pertamina 54.601',
            'notes' => 'Mobil Innova tim teknisi',
            'is_late' => false,
            'verification_status' => 'VALID',
            'verified_by' => $finance->id,
            'verified_at' => Carbon::now()->subDays(6),
            'verification_notes' => 'Struk struk fisik & QR tercetak jelas.',
        ]);

        // Expense 2 for CA 1: Material isolasi & kabel duct
        $exp2 = Expense::create([
            'cash_advance_id' => $caActive->id,
            'user_id' => $karyawan->id,
            'transaction_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'description' => 'Kabel Ducting PVC & Spiral Conduit 2 inch',
            'category' => 'material',
            'amount' => 1450000,
            'vendor_name' => 'Toko Teknik Sejahtera',
            'vendor_tax_number' => '01.234.567.8-091.000',
            'notes' => 'Kebutuhan perapian wiring panel inverter',
            'is_late' => false,
            'verification_status' => 'PENDING',
        ]);

        // Expense 3 for CA 1: Sewa Alat Scaffolding (Terlambat catat untuk demo badge TERLAMBAT)
        $exp3 = Expense::create([
            'cash_advance_id' => $caActive->id,
            'user_id' => $karyawan->id,
            'transaction_date' => Carbon::now()->subDays(6)->format('Y-m-d'),
            'description' => 'Sewa Scaffolding 3 set 5 hari',
            'category' => 'sewa_alat',
            'amount' => 1800000,
            'vendor_name' => 'CV Karya Gemilang Scaffolding',
            'notes' => 'Pekerjaan kabel ladder ketinggian 6 meter',
            'is_late' => true, // Dicatat lebih dari 1 hari
            'verification_status' => 'PENDING',
        ]);

        // Top-up for CA 1: Top-up requested by Ahmad
        $topup = TopUp::create([
            'cash_advance_id' => $caActive->id,
            'user_id' => $karyawan->id,
            'amount' => 3000000,
            'reason' => 'Perpanjangan sewa alat crane kecil untuk lifting motor 160kW karena jadwal shutdown mundur 2 hari',
            'status' => 'DISBURSED',
            'approved_by_atasan' => $atasan->id,
            'atasan_approved_at' => Carbon::now()->subDays(3),
            'approved_by_finance' => $finance->id,
            'finance_approved_at' => Carbon::now()->subDays(2),
            'disbursed_by' => $finance->id,
            'disbursed_at' => Carbon::now()->subDays(2),
            'disbursement_ref' => 'TRF-BCA-902231',
            'disbursement_notes' => 'Dana top-up cair via transfer BCA.',
        ]);

        // Sample CA 2: NEED_SETTLEMENT (Budi Santoso - Proyek Semen Indonesia Tuban)
        $caSettlement = CashAdvance::create([
            'ca_number' => 'CA-202610-0002',
            'user_id' => $budi->id,
            'project_name' => 'Preventive Maintenance Drive Semen Indonesia Tuban',
            'project_code' => 'PRJ-2026-SIT02',
            'purpose' => 'Akomodasi teknisi & penggantian filter pendingin fan',
            'requested_amount' => 5000000,
            'approved_amount' => 5000000,
            'status' => 'NEED_SETTLEMENT',
            'approved_by' => $atasan->id,
            'approved_at' => Carbon::now()->subDays(25),
            'disbursed_by' => $finance->id,
            'disbursed_at' => Carbon::now()->subDays(24),
            'disbursement_ref' => 'TRF-MDR-776110',
            'due_date' => Carbon::now()->addDays(36),
            'project_completed_at' => Carbon::now()->subDays(1),
        ]);

        Expense::create([
            'cash_advance_id' => $caSettlement->id,
            'user_id' => $budi->id,
            'transaction_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
            'description' => 'Hotel Fave Tuban 4 malam 2 kamar',
            'category' => 'konsumsi',
            'amount' => 2400000,
            'vendor_name' => 'Fave Hotel Tuban',
            'notes' => '2 teknisi lapangan',
            'verification_status' => 'VALID',
            'verified_by' => $finance->id,
            'verified_at' => Carbon::now()->subDays(15),
        ]);

        Expense::create([
            'cash_advance_id' => $caSettlement->id,
            'user_id' => $budi->id,
            'transaction_date' => Carbon::now()->subDays(18)->format('Y-m-d'),
            'description' => 'Makan & Konsumsi Tim 5 Hari',
            'category' => 'konsumsi',
            'amount' => 1100000,
            'vendor_name' => 'RM Asri Tuban',
            'verification_status' => 'VALID',
            'verified_by' => $finance->id,
            'verified_at' => Carbon::now()->subDays(15),
        ]);

        // Sisa saldo CA 2: Rp 5.000.000 - Rp 3.500.000 = Rp 1.500.000 (Wajib di-refund)

        // Sample CA 3: PENDING_APPROVAL (Indri Handayani as requester -> approval rises to Direktur!)
        $caAtasan = CashAdvance::create([
            'ca_number' => 'CA-202611-0003',
            'user_id' => $atasan->id,
            'project_name' => 'Survey & Site Meeting Proyek PLTU Paiton',
            'project_code' => 'PRJ-2026-PAI05',
            'purpose' => 'Kunjungan teknis manajemen operasional dan koordinasi re-engineering drive boiler',
            'requested_amount' => 4500000,
            'status' => 'PENDING_APPROVAL',
        ]);

        // 4. Initial Audit Log
        AuditLog::create([
            'user_id' => $superAdmin->id,
            'user_name' => $superAdmin->name,
            'user_role' => $superAdmin->role,
            'action' => 'SYSTEM_INIT',
            'record_type' => 'System',
            'record_id' => 1,
            'description' => 'Sistem Cash Advance & Settlement PT Technodrives diinisialisasi.',
            'created_at' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        // 5. Sample Notifications
        SystemNotification::create([
            'user_id' => $direktur->id,
            'title' => 'Persetujuan Khusus CA Masuk',
            'message' => 'Pengajuan CA-202611-0003 oleh Atasan (Indri Handayani) memerlukan persetujuan Direktur.',
            'type' => 'warning',
            'link' => '/cash-advances/' . $caAtasan->id,
            'is_read' => false,
            'created_at' => now(),
        ]);

        SystemNotification::create([
            'user_id' => $finance->id,
            'title' => 'Verifikasi Struk Menunggu',
            'message' => 'Ada 2 struk pengeluaran baru pada CA-202611-0001 siap diverifikasi.',
            'type' => 'info',
            'link' => '/cash-advances/' . $caActive->id,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }
}
