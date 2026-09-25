<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\ProgressUpdate;
use App\Models\DailyProgressLog;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Schedule;
use App\Models\Notification;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $admin = User::create([
            'name' => 'Super Admin (Owner)',
            'email' => 'admin@solvia.nova',
            'password' => Hash::make('password'),
            'role' => 'SUPER_ADMIN',
            'status' => 'ACTIVE',
            'phone' => '+6281234567890',
        ]);

        $contentCreator = User::create([
            'name' => 'Sara Content',
            'email' => 'content@solvia.nova',
            'password' => Hash::make('password'),
            'role' => 'CONTENT_CREATOR',
            'status' => 'ACTIVE',
            'phone' => '+6281234567891',
        ]);

        $designer = User::create([
            'name' => 'David Designer',
            'email' => 'designer@solvia.nova',
            'password' => Hash::make('password'),
            'role' => 'DESIGNER',
            'status' => 'ACTIVE',
            'phone' => '+6281234567892',
        ]);

        $frontendDev = User::create([
            'name' => 'Farhan Frontend',
            'email' => 'frontend@solvia.nova',
            'password' => Hash::make('password'),
            'role' => 'FRONTEND_DEV',
            'status' => 'ACTIVE',
            'phone' => '+6281234567893',
        ]);

        $backendDev = User::create([
            'name' => 'Budi Backend',
            'email' => 'backend@solvia.nova',
            'password' => Hash::make('password'),
            'role' => 'BACKEND_DEV',
            'status' => 'ACTIVE',
            'phone' => '+6281234567894',
        ]);

        $iotEng = User::create([
            'name' => 'Irwan IoT',
            'email' => 'iot@solvia.nova',
            'password' => Hash::make('password'),
            'role' => 'IOT_ENGINEER',
            'status' => 'ACTIVE',
            'phone' => '+6281234567895',
        ]);

        $joki = User::create([
            'name' => 'Joko Joki Freelance',
            'email' => 'joki@solvia.nova',
            'password' => Hash::make('password'),
            'role' => 'JOKI',
            'status' => 'ACTIVE',
            'phone' => '+6281234567896',
        ]);

        // 2. Financial Accounts
        $bca = FinancialAccount::create([
            'name' => 'BCA Corporate',
            'type' => 'BANK',
            'account_number' => '8820912389',
            'opening_balance' => 50000000,
            'current_balance' => 50000000,
        ]);

        $mandiri = FinancialAccount::create([
            'name' => 'Mandiri Business',
            'type' => 'BANK',
            'account_number' => '137000889211',
            'opening_balance' => 25000000,
            'current_balance' => 25000000,
        ]);

        $jago = FinancialAccount::create([
            'name' => 'Bank Jago Operational',
            'type' => 'BANK',
            'account_number' => '1092837411',
            'opening_balance' => 10000000,
            'current_balance' => 10000000,
        ]);

        $gopay = FinancialAccount::create([
            'name' => 'GoPay Corporate',
            'type' => 'E_WALLET',
            'account_number' => '081234567890',
            'opening_balance' => 3000000,
            'current_balance' => 3000000,
        ]);

        $cash = FinancialAccount::create([
            'name' => 'Petty Cash Office',
            'type' => 'CASH',
            'account_number' => 'CASH-01',
            'opening_balance' => 2000000,
            'current_balance' => 2000000,
        ]);

        // 3. Financial Categories
        $catProjectPayment = FinancialCategory::create(['name' => 'Project Payment', 'type' => 'INCOME']);
        $catService = FinancialCategory::create(['name' => 'Service & Consulting', 'type' => 'INCOME']);
        $catOtherIncome = FinancialCategory::create(['name' => 'Other Income', 'type' => 'INCOME']);

        $catSalary = FinancialCategory::create(['name' => 'Salary & Freelance Fee', 'type' => 'EXPENSE']);
        $catSoftware = FinancialCategory::create(['name' => 'Software & Subscriptions', 'type' => 'EXPENSE']);
        $catVPS = FinancialCategory::create(['name' => 'VPS & Server Infrastructure', 'type' => 'EXPENSE']);
        $catDomain = FinancialCategory::create(['name' => 'Domain & SSL', 'type' => 'EXPENSE']);
        $catProjectCost = FinancialCategory::create(['name' => 'Project Cost', 'type' => 'EXPENSE']);
        $catOperational = FinancialCategory::create(['name' => 'Operational Expenses', 'type' => 'EXPENSE']);

        // 4. Projects
        $project1 = Project::create([
            'name' => 'Smart Agriculture IoT Platform',
            'code' => 'PRJ-AGRI-01',
            'client' => 'PT Agro Tech Nusantara',
            'description' => 'Sistem monitoring kelembapan tanah, suhu, dan otomatisasi irigasi menggunakan ESP32 & Laravel Web Dashboard.',
            'start_date' => Carbon::now()->subDays(15),
            'deadline' => Carbon::now()->addDays(15),
            'status' => 'ACTIVE',
            'progress' => 0,
            'created_by' => $admin->id,
        ]);
        $project1->members()->attach([$iotEng->id, $backendDev->id, $frontendDev->id, $designer->id, $joki->id]);

        $project2 = Project::create([
            'name' => 'Solvia Corporate Redesign & PWA',
            'code' => 'PRJ-SOLV-02',
            'client' => 'Internal Solvia.Nova',
            'description' => 'Pembaruan UI/UX website perusahaan dan integrasi PWA mobile dashboard.',
            'start_date' => Carbon::now()->subDays(10),
            'deadline' => Carbon::now()->addDays(5),
            'status' => 'ACTIVE',
            'progress' => 0,
            'created_by' => $admin->id,
        ]);
        $project2->members()->attach([$designer->id, $frontendDev->id, $contentCreator->id]);

        // 5. Tasks for Project 1
        $task1 = Task::create([
            'project_id' => $project1->id,
            'title' => 'Perancangan Skema Hardware ESP32 & Sensor Soil Moisture',
            'description' => 'Buat diagram pengkabelan dan uji coba kalibrasi sensor kelembapan tanah.',
            'priority' => 'HIGH',
            'status' => 'DONE',
            'progress' => 100,
            'deadline' => Carbon::now()->subDays(2),
            'created_by' => $admin->id,
        ]);
        $task1->assignees()->attach($iotEng->id);

        $task2 = Task::create([
            'project_id' => $project1->id,
            'title' => 'REST API Endpoint Ingestion Telemetri IoT',
            'description' => 'Buat endpoint `/api/v1/telemetry` dengan autentikasi API Key & rate limiting.',
            'priority' => 'URGENT',
            'status' => 'BLOCKED',
            'progress' => 40,
            'deadline' => Carbon::now()->addDays(2),
            'blocker_reason' => 'Spesifikasi payload MQTT dari provider Gateway durasi kirim belum fix.',
            'blocker_priority' => 'HIGH',
            'created_by' => $admin->id,
        ]);
        $task2->assignees()->attach($backendDev->id);

        $task3 = Task::create([
            'project_id' => $project1->id,
            'title' => 'UI Dashboard Monitoring Realtime',
            'description' => 'Buat grafik realtime temperatur & kelembapan tanah menggunakan Livewire & Alpine.js.',
            'priority' => 'HIGH',
            'status' => 'WAITING_REVIEW',
            'progress' => 90,
            'deadline' => Carbon::now()->addDays(1),
            'created_by' => $admin->id,
        ]);
        $task3->assignees()->attach($frontendDev->id);

        $task4 = Task::create([
            'project_id' => $project1->id,
            'title' => 'Testing & Assembly Box Panel IoT Field Unit',
            'description' => 'Rakit 5 unit panel waterproofing untuk pemasangan di lahan pertanian.',
            'priority' => 'MEDIUM',
            'status' => 'IN_PROGRESS',
            'progress' => 50,
            'deadline' => Carbon::now()->addDays(6),
            'created_by' => $admin->id,
        ]);
        $task4->assignees()->attach($joki->id);

        // Tasks for Project 2
        $task5 = Task::create([
            'project_id' => $project2->id,
            'title' => 'Design System & UI Component Figma',
            'description' => 'Buat warna semantic, typography token, dan button state untuk Solvia OS.',
            'priority' => 'HIGH',
            'status' => 'DONE',
            'progress' => 100,
            'deadline' => Carbon::now()->subDays(4),
            'created_by' => $admin->id,
        ]);
        $task5->assignees()->attach($designer->id);

        $task6 = Task::create([
            'project_id' => $project2->id,
            'title' => 'Penulisan Copywriting Landing Page & Pitching Kit',
            'description' => 'Susun narasi value proposition Solvia.Nova OS untuk halaman muka.',
            'priority' => 'MEDIUM',
            'status' => 'REVISION',
            'progress' => 70,
            'deadline' => Carbon::now()->addDays(1),
            'revision_notes' => 'Tolong pertegas mengenai keunggulan kecepatan operational command center.',
            'created_by' => $admin->id,
        ]);
        $task6->assignees()->attach($contentCreator->id);

        // Recalculate Project Progresses
        $project1->recalculateProgress();
        $project2->recalculateProgress();

        // 6. Progress Updates History
        ProgressUpdate::create([
            'task_id' => $task3->id,
            'user_id' => $frontendDev->id,
            'progress' => 90,
            'description' => 'Sudah menyelesaikan widget chart realtime dan statistik telemetry.',
            'completed_notes' => 'Integrasi WebSocket & polling Livewire selesai.',
            'next_plan' => 'Minor polish spacing mobile.',
            'blocker' => null,
            'attachment_url' => 'https://via.placeholder.com/800x450.png?text=UI+Dashboard+Realtime',
        ]);

        // 7. Daily Progress Logs
        DailyProgressLog::create([
            'user_id' => $frontendDev->id,
            'date' => Carbon::today(),
            'project_id' => $project1->id,
            'task_id' => $task3->id,
            'progress' => 90,
            'work_done' => 'Menyelesaikan dashboard telemetry realtime.',
            'next_plan' => 'Submit review ke Super Admin.',
            'blocker' => 'Tidak ada.',
        ]);

        DailyProgressLog::create([
            'user_id' => $designer->id,
            'date' => Carbon::today(),
            'project_id' => $project2->id,
            'task_id' => $task5->id,
            'progress' => 100,
            'work_done' => 'Design system Figma selesai.',
            'next_plan' => 'Membantu tim frontend eksekusi icon set.',
            'blocker' => 'Tidak ada.',
        ]);

        // 8. Financial Transactions
        FinancialTransaction::create([
            'transaction_code' => 'INC-202609-001',
            'date' => Carbon::now()->subDays(10),
            'type' => 'INCOME',
            'category_id' => $catProjectPayment->id,
            'source_destination' => 'PT Agro Tech Nusantara',
            'description' => 'DP 50% Smart Agriculture IoT Platform Project',
            'amount' => 35000000,
            'financial_account_id' => $bca->id,
            'project_id' => $project1->id,
            'created_by' => $admin->id,
        ]);

        FinancialTransaction::create([
            'transaction_code' => 'EXP-202609-001',
            'date' => Carbon::now()->subDays(5),
            'type' => 'EXPENSE',
            'category_id' => $catProjectCost->id,
            'source_destination' => 'Toko Komponen IoT Surabaya',
            'description' => 'Pembelian 10x ESP32, 20x Sensor Soil Moisture & Panel Box',
            'amount' => 4500000,
            'financial_account_id' => $bca->id,
            'project_id' => $project1->id,
            'created_by' => $admin->id,
        ]);

        FinancialTransaction::create([
            'transaction_code' => 'EXP-202609-002',
            'date' => Carbon::now()->subDays(3),
            'type' => 'EXPENSE',
            'category_id' => $catSoftware->id,
            'source_destination' => 'Figma Inc.',
            'description' => 'Figma Professional Organization License Monthly',
            'amount' => 750000,
            'financial_account_id' => $gopay->id,
            'created_by' => $admin->id,
        ]);

        FinancialTransaction::create([
            'transaction_code' => 'TRF-202609-001',
            'date' => Carbon::now()->subDays(2),
            'type' => 'TRANSFER',
            'category_id' => null,
            'source_destination' => 'Internal Transfer BCA to Jago',
            'description' => 'Alokasi Dana Operasional Mingguan ke Bank Jago',
            'amount' => 5000000,
            'financial_account_id' => $bca->id,
            'target_account_id' => $jago->id,
            'created_by' => $admin->id,
        ]);

        // 9. Assets & Infrastructure
        $asset1 = Asset::create([
            'name' => 'MacBook Pro M2 Max 16-inch',
            'asset_code' => 'AST-HW-001',
            'type' => 'HARDWARE',
            'serial_spec' => '32GB RAM / 1TB SSD / Space Gray',
            'owner' => 'Solvia.Nova',
            'responsible_user_id' => $frontendDev->id,
            'status' => 'IN_USE',
            'cost' => 42000000,
            'notes' => 'Fasilitas kerja developer.',
        ]);

        $asset2 = Asset::create([
            'name' => 'Kit Testing Sensor & Gateway ESP32',
            'asset_code' => 'AST-IOT-002',
            'type' => 'IOT',
            'serial_spec' => 'Bundle 5x ESP32-WROOM-32U + RS485 Modbus',
            'owner' => 'Solvia.Nova',
            'responsible_user_id' => $iotEng->id,
            'status' => 'IN_USE',
            'cost' => 2500000,
            'project_id' => $project1->id,
        ]);

        $asset3 = Asset::create([
            'name' => 'VPS Production Server IDCloudHost',
            'asset_code' => 'AST-INF-003',
            'type' => 'INFRASTRUCTURE',
            'serial_spec' => '8 vCPU / 16GB RAM / 200GB NVMe (IP: 103.152.118.42)',
            'owner' => 'Solvia.Nova',
            'responsible_user_id' => $backendDev->id,
            'status' => 'ACTIVE',
            'cost' => 6000000,
            'expiry_date' => Carbon::now()->addDays(8), // Expiry soon!
            'notes' => 'Server staging & production Solvia Apps.',
        ]);

        $asset4 = Asset::create([
            'name' => 'Domain Utama solvia.nova',
            'asset_code' => 'AST-INF-004',
            'type' => 'INFRASTRUCTURE',
            'serial_spec' => 'Registrar: Cloudflare Inc.',
            'owner' => 'Solvia.Nova',
            'responsible_user_id' => $admin->id,
            'status' => 'ACTIVE',
            'cost' => 350000,
            'expiry_date' => Carbon::now()->addDays(20), // Expiry soon!
        ]);

        AssetAssignment::create([
            'asset_id' => $asset1->id,
            'user_id' => $frontendDev->id,
            'status' => 'ASSIGNED',
            'assigned_date' => Carbon::now()->subMonths(3),
            'notes' => 'Penyerahan laptop inventaris awal.',
        ]);

        // 10. Schedules
        Schedule::create([
            'title' => 'Weekly Operational Command Sync',
            'date' => Carbon::today(),
            'time' => '10:00:00',
            'type' => 'MEETING',
            'description' => 'Evaluasi progress mingguan tim & review blocker API telemetri IoT.',
            'created_by' => $admin->id,
        ]);

        Schedule::create([
            'title' => 'Deadline Review UI Dashboard Telemetry',
            'date' => Carbon::today(),
            'time' => '15:30:00',
            'type' => 'TASK_DEADLINE',
            'project_id' => $project1->id,
            'task_id' => $task3->id,
            'description' => 'Target pengujian UI dashboard oleh Super Admin.',
            'created_by' => $admin->id,
        ]);

        Schedule::create([
            'title' => 'VPS Server Renewal Due Date',
            'date' => Carbon::now()->addDays(8),
            'time' => '09:00:00',
            'type' => 'MAINTENANCE',
            'asset_id' => $asset3->id,
            'description' => 'Jadwal perpanjangan VPS IDCloudHost sebelum expired.',
            'created_by' => $admin->id,
        ]);

        // 11. Notifications
        Notification::create([
            'user_id' => $admin->id,
            'type' => 'BLOCKER',
            'title' => '🚨 Task Terhambat (Blocked)',
            'message' => 'Backend Developer melaporkan blocker pada task "REST API Endpoint Ingestion Telemetri IoT".',
            'link' => '/tasks?task_id=' . $task2->id,
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $admin->id,
            'type' => 'REVIEW',
            'title' => '🟠 Pekerjaan Menunggu Review',
            'message' => 'Farhan Frontend telah mengirimkan hasil kerja untuk task "UI Dashboard Monitoring Realtime".',
            'link' => '/tasks?task_id=' . $task3->id,
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $admin->id,
            'type' => 'EXPIRY',
            'title' => '⚠️ VPS Host Expired dalam 8 hari',
            'message' => 'VPS Production Server IDCloudHost akan jatuh tempo pada ' . Carbon::now()->addDays(8)->format('d M Y') . '.',
            'link' => '/assets?asset_id=' . $asset3->id,
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $frontendDev->id,
            'type' => 'TASK',
            'title' => 'Task Baru Ditugaskan',
            'message' => 'Anda ditugaskan pada task "UI Dashboard Monitoring Realtime" di project Smart Agriculture IoT Platform.',
            'link' => '/tasks?task_id=' . $task3->id,
            'is_read' => true,
        ]);

        // 12. Activity Logs
        ActivityLog::log('PROJECT_CREATED', 'Super Admin membuat project "Smart Agriculture IoT Platform".', $project1, $admin);
        ActivityLog::log('TASK_BLOCKED', 'Backend Developer melaporkan kendala pada REST API Ingestion.', $task2, $backendDev);
        ActivityLog::log('TASK_SUBMITTED', 'Farhan Frontend mengirimkan hasil pekerjaan "UI Dashboard Monitoring Realtime" untuk direview.', $task3, $frontendDev);
        ActivityLog::log('FINANCE_INCOME', 'Super Admin mencatat pemasukan Rp35.000.000 dari PT Agro Tech Nusantara.', null, $admin);
    }
}
