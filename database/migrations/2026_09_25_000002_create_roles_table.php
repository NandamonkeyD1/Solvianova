<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. PROJECT_MANAGER
            $table->string('display_name'); // e.g. Project Manager
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // Seed initial default roles into master roles table
        $defaultRoles = [
            ['name' => 'SUPER_ADMIN', 'display_name' => 'Super Admin', 'description' => 'Akses penuh ke seluruh sistem dan kontrol perusahaan.'],
            ['name' => 'CONTENT_CREATOR', 'display_name' => 'Content Creator', 'description' => 'Membuat konten dan mengelola tugas kreatif.'],
            ['name' => 'DESIGNER', 'display_name' => 'UI/UX Designer', 'description' => 'Desain aset visual, UI/UX, dan mockups.'],
            ['name' => 'FRONTEND_DEV', 'display_name' => 'Frontend Developer', 'description' => 'Pengembangan antarmuka web dan aplikasi.'],
            ['name' => 'BACKEND_DEV', 'display_name' => 'Backend Developer', 'description' => 'Pengembangan server, API, dan basis data.'],
            ['name' => 'IOT_ENGINEER', 'display_name' => 'IoT Engineer', 'description' => 'Pengembangan perangkat keras dan sistem IoT.'],
            ['name' => 'JOKI', 'display_name' => 'Joki Freelance', 'description' => 'Pengerjaan tugas spesifik eksternal.'],
        ];

        foreach ($defaultRoles as $r) {
            DB::table('roles')->insert([
                'name' => $r['name'],
                'display_name' => $r['display_name'],
                'description' => $r['description'],
                'permissions' => json_encode(['project.view', 'task.view', 'progress.view', 'progress.update', 'schedule.view', 'notification.view']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
