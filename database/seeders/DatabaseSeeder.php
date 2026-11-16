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

        $direktur = User::firstOrCreate(['email' => 'direktur@technodrives.co.id'], [
            'name' => 'Bambang Soedirgo',
            'password' => $commonPassword,
            'role' => 'direktur',
            'department' => ['Direksi', 'Manajemen Eksekutif'],
            'avatar' => 'avatars/bambang.jpg',
            'phone' => '081122334455',
            'bank_name' => 'BCA',
            'bank_account_number' => '1112223334',
            'bank_account_holder' => 'Bambang Soedirgo',
        ]);

        $superAdmin = User::firstOrCreate(['email' => 'superadmin@technodrives.co.id'], [
            'name' => 'Super Admin IT',
            'password' => $commonPassword,
            'role' => 'super_admin',
            'department' => ['IT & Systems', 'Infrastruktur Jaringan'],
            'avatar' => 'avatars/superadmin.jpg',
            'phone' => '081234567890',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '9988776655',
            'bank_account_holder' => 'Super Admin IT',
        ]);

        $finance = User::firstOrCreate(['email' => 'finance@technodrives.co.id'], [
            'name' => 'Dewi Lestari',
            'password' => $commonPassword,
            'role' => 'finance',
            'manager_id' => $direktur->id,
            'department' => ['Finance & Accounting', 'Perpajakan'],
            'avatar' => 'avatars/dewi.jpg',
            'phone' => '081399887766',
            'bank_name' => 'BCA',
            'bank_account_number' => '5544332211',
            'bank_account_holder' => 'Dewi Lestari',
        ]);

        $atasan = User::firstOrCreate(['email' => 'atasan@technodrives.co.id'], [
            'name' => 'Indri Handayani',
            'password' => $commonPassword,
            'role' => 'atasan',
            'manager_id' => $direktur->id,
            'department' => ['Operasional & Engineering', 'Perencanaan Proyek'],
            'avatar' => 'avatars/indri.jpg',
            'phone' => '081288776655',
            'bank_name' => 'BCA',
            'bank_account_number' => '3322114455',
            'bank_account_holder' => 'Indri Handayani',
        ]);

        $ahmad = User::firstOrCreate(['email' => 'ahmad@technodrives.co.id'], [
            'name' => 'Ahmad Maulidun',
            'password' => $commonPassword,
            'role' => 'karyawan',
            'manager_id' => $atasan->id,
            'department' => ['Engineering & Commissioning', 'Instalasi Lapangan'],
            'avatar' => 'avatars/ahmad.jpg',
            'phone' => '081577665544',
            'bank_name' => 'BCA',
            'bank_account_number' => '7788990011',
            'bank_account_holder' => 'Ahmad Maulidun',
        ]);

        $budi = User::firstOrCreate(['email' => 'budi@technodrives.co.id'], [
            'name' => 'Budi Santoso',
            'password' => $commonPassword,
            'role' => 'karyawan',
            'manager_id' => $atasan->id,
            'department' => ['Field Service', 'Pemeliharaan Listrik'],
            'avatar' => 'avatars/budi.jpg',
            'phone' => '081622334455',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '8899001122',
            'bank_account_holder' => 'Budi Santoso',
        ]);

    }
}
