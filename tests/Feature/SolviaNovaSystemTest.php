<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\ProgressUpdate;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\Asset;
use App\Models\Notification;
use Livewire\Livewire;
use App\Livewire\Tasks;

class SolviaNovaSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_renders_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_dashboard_and_command_center()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Company Command Center');
    }

    public function test_joki_cannot_access_finance_or_user_management()
    {
        $joki = User::where('role', 'JOKI')->first();
        
        $responseFinance = $this->actingAs($joki)->get('/finance');
        $responseFinance->assertStatus(403);

        $responseUsers = $this->actingAs($joki)->get('/users');
        $responseUsers->assertStatus(403);

        // Joki can access tasks
        $responseTasks = $this->actingAs($joki)->get('/tasks');
        $responseTasks->assertStatus(200);
    }

    public function test_task_progress_update_creates_history_record()
    {
        $frontendDev = User::where('role', 'FRONTEND_DEV')->first();
        $task = Task::first();

        Livewire::actingAs($frontendDev)
            ->test(Tasks::class)
            ->call('openTaskDetail', $task->id)
            ->set('progressPercent', 95)
            ->set('updateDescription', 'Fixing responsive layout on mobile views')
            ->set('nextPlan', 'Submit to Super Admin')
            ->call('saveProgressUpdate')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('progress_updates', [
            'task_id' => $task->id,
            'user_id' => $frontendDev->id,
            'progress' => 95,
            'description' => 'Fixing responsive layout on mobile views',
        ]);
    }

    public function test_full_operational_workflow_plan_work_review_revision_approve()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();
        $frontendDev = User::where('role', 'FRONTEND_DEV')->first();
        $project = Project::first();

        // 1. Super Admin creates Task & assigns to Member
        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Test Workflow Landing Page',
            'description' => 'Implement landing page UI',
            'priority' => 'HIGH',
            'status' => 'TODO',
            'progress' => 0,
            'created_by' => $admin->id,
        ]);
        $task->assignees()->attach($frontendDev->id);

        // 2. Member works & updates progress
        Livewire::actingAs($frontendDev)
            ->test(Tasks::class)
            ->call('openTaskDetail', $task->id)
            ->set('progressPercent', 80)
            ->set('updateDescription', 'Completed hero section and features grid')
            ->call('saveProgressUpdate');

        $task->refresh();
        $this->assertEquals(80, $task->progress);
        $this->assertEquals('IN_PROGRESS', $task->status);

        // 3. Member submits for review
        Livewire::actingAs($frontendDev)
            ->test(Tasks::class)
            ->call('openTaskDetail', $task->id)
            ->call('submitForReview');

        $task->refresh();
        $this->assertEquals('WAITING_REVIEW', $task->status);

        // 4. Super Admin reviews & requests revision
        Livewire::actingAs($admin)
            ->test(Tasks::class)
            ->call('openTaskDetail', $task->id)
            ->set('revisionFeedback', 'Perbaiki margin pada tombol call to action')
            ->call('submitRevisionNotes');

        $task->refresh();
        $this->assertEquals('REVISION', $task->status);
        $this->assertEquals('Perbaiki margin pada tombol call to action', $task->revision_notes);

        // 5. Member updates work to 100% and re-submits
        Livewire::actingAs($frontendDev)
            ->test(Tasks::class)
            ->call('openTaskDetail', $task->id)
            ->set('progressPercent', 100)
            ->set('updateDescription', 'Margin fixed on CTA button')
            ->call('saveProgressUpdate')
            ->call('submitForReview');

        $task->refresh();
        $this->assertEquals('WAITING_REVIEW', $task->status);

        // 6. Super Admin approves task
        Livewire::actingAs($admin)
            ->test(Tasks::class)
            ->call('openTaskDetail', $task->id)
            ->call('approveTaskDirect');

        $task->refresh();
        $this->assertEquals('DONE', $task->status);
        $this->assertEquals(100, $task->progress);
    }

    public function test_financial_balance_auto_recalculation()
    {
        $account = FinancialAccount::create([
            'name' => 'Test BCA Account',
            'type' => 'BANK',
            'opening_balance' => 10000000,
            'current_balance' => 10000000,
        ]);

        $admin = User::where('role', 'SUPER_ADMIN')->first();

        // Income of 5,000,000
        FinancialTransaction::create([
            'transaction_code' => 'TEST-INC-001',
            'date' => now(),
            'type' => 'INCOME',
            'amount' => 5000000,
            'financial_account_id' => $account->id,
            'created_by' => $admin->id,
        ]);

        $account->refresh();
        $this->assertEquals(15000000, (float) $account->current_balance);

        // Expense of 2,000,000
        FinancialTransaction::create([
            'transaction_code' => 'TEST-EXP-001',
            'date' => now(),
            'type' => 'EXPENSE',
            'amount' => 2000000,
            'financial_account_id' => $account->id,
            'created_by' => $admin->id,
        ]);

        $account->refresh();
        $this->assertEquals(13000000, (float) $account->current_balance);
    }

    public function test_project_progress_recalculation_from_tasks()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();
        $project = Project::create([
            'name' => 'Test Recalc Project',
            'code' => 'TEST-PRJ-01',
            'status' => 'ACTIVE',
            'progress' => 0,
            'created_by' => $admin->id,
        ]);

        $task1 = Task::create([
            'project_id' => $project->id,
            'title' => 'Task A',
            'status' => 'DONE',
            'progress' => 100,
            'created_by' => $admin->id,
        ]);

        $task2 = Task::create([
            'project_id' => $project->id,
            'title' => 'Task B',
            'status' => 'IN_PROGRESS',
            'progress' => 50,
            'created_by' => $admin->id,
        ]);

        $project->recalculateProgress();
        $project->refresh();

        // Average of 100 and 50 is 75%
        $this->assertEquals(75, $project->progress);
    }

    public function test_asset_expiry_detection()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();
        $assetExpiringSoon = Asset::create([
            'name' => 'Expiring SSL Certificate',
            'asset_code' => 'AST-SSL-TEST',
            'type' => 'INFRASTRUCTURE',
            'cost' => 500000,
            'expiry_date' => now()->addDays(5),
            'status' => 'ACTIVE',
        ]);

        $this->assertTrue($assetExpiringSoon->isExpiringSoon(30));
        $this->assertFalse($assetExpiringSoon->isExpired());
    }

    public function test_user_registration_works()
    {
        Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('name', 'Developer Baru')
            ->set('email', 'devbaru@solvia.nova')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'FRONTEND_DEV')
            ->set('phone', '081299990000')
            ->call('register')
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'devbaru@solvia.nova',
            'role' => 'FRONTEND_DEV',
        ]);
    }

    public function test_all_livewire_components_render_for_super_admin()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();
        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Dashboard::class)->assertStatus(200);
        Livewire::test(\App\Livewire\Projects::class)->assertStatus(200);
        Livewire::test(\App\Livewire\Tasks::class)->assertStatus(200);
        Livewire::test(\App\Livewire\DailyProgress::class)->assertStatus(200);
        Livewire::test(\App\Livewire\ScheduleCalendar::class)->assertStatus(200);
        Livewire::test(\App\Livewire\NotificationsCenter::class)->assertStatus(200);
        Livewire::test(\App\Livewire\FinanceManager::class)->assertStatus(200);
        Livewire::test(\App\Livewire\AssetManager::class)->assertStatus(200);
        Livewire::test(\App\Livewire\UserManager::class)->assertStatus(200);
        Livewire::test(\App\Livewire\GlobalSearch::class)->assertStatus(200);
    }

    public function test_livewire_projects_crud()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Projects::class)
            ->set('name', 'Project Test Alpha')
            ->set('code', 'PRJ-ALPHA-99')
            ->set('client', 'PT Client Alpha')
            ->set('description', 'Testing project creation')
            ->call('saveProject')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('projects', [
            'code' => 'PRJ-ALPHA-99',
            'name' => 'Project Test Alpha',
        ]);
    }

    public function test_livewire_finance_manager_crud()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();

        $account = FinancialAccount::create([
            'name' => 'Rekening Mandiri Utama',
            'type' => 'BANK',
            'account_number' => '1234567890',
            'opening_balance' => 25000000,
            'current_balance' => 25000000,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\FinanceManager::class)
            ->set('financial_account_id', $account->id)
            ->set('amount', 5000000)
            ->set('category_name', 'Project Down Payment')
            ->call('saveMoneyIn')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('financial_transactions', [
            'amount' => 5000000,
            'type' => 'INCOME',
        ]);
    }

    public function test_livewire_asset_manager_crud()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\AssetManager::class)
            ->set('name', 'MacBook Pro M3 Max')
            ->set('asset_code', 'AST-MBP-001')
            ->set('type', 'HARDWARE')
            ->set('cost', 45000000)
            ->call('saveAsset')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('assets', [
            'asset_code' => 'AST-MBP-001',
            'name' => 'MacBook Pro M3 Max',
        ]);
    }

    public function test_livewire_schedule_calendar_crud()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\ScheduleCalendar::class)
            ->set('title', 'Weekly Team Sync')
            ->set('date', now()->format('Y-m-d'))
            ->set('type', 'MEETING')
            ->call('saveSchedule')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('schedules', [
            'title' => 'Weekly Team Sync',
        ]);
    }

    public function test_livewire_user_manager_crud()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\UserManager::class)
            ->set('name', 'Member Tambahan')
            ->set('email', 'member.baru@solvia.nova')
            ->set('role', 'BACKEND_DEV')
            ->set('password', 'password123')
            ->call('saveUser')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('users', [
            'email' => 'member.baru@solvia.nova',
            'role' => 'BACKEND_DEV',
        ]);
    }

    public function test_manual_account_creation_and_management()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\FinanceManager::class)
            ->call('openAccountModal')
            ->set('account_name', 'BCA Kas Utama')
            ->set('account_type', 'BANK')
            ->set('account_number', '9876543210')
            ->set('account_opening_balance', 50000000)
            ->call('saveAccount')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('financial_accounts', [
            'name' => 'BCA Kas Utama',
            'account_number' => '9876543210',
            'opening_balance' => 50000000,
        ]);
    }

    public function test_transfer_with_manual_target_account()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();
        $sourceAccount = FinancialAccount::create([
            'name' => 'Kas Sumber',
            'type' => 'CASH',
            'opening_balance' => 10000000,
            'current_balance' => 10000000,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\FinanceManager::class)
            ->set('financial_account_id', $sourceAccount->id)
            ->set('target_account_name', 'Rekening Target Manual')
            ->set('amount', 2500000)
            ->call('saveTransfer')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('financial_accounts', [
            'name' => 'Rekening Target Manual',
        ]);

        $this->assertDatabaseHas('financial_transactions', [
            'type' => 'TRANSFER',
            'amount' => 2500000,
        ]);
    }

    public function test_super_admin_can_add_custom_role()
    {
        $admin = User::where('role', 'SUPER_ADMIN')->first();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\UserManager::class)
            ->set('name', 'DevOps Specialist')
            ->set('email', 'devops@solvia.nova')
            ->set('custom_role', 'DEVOPS_ENGINEER')
            ->set('password', 'password123')
            ->call('saveUser')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('users', [
            'email' => 'devops@solvia.nova',
            'role' => 'DEVOPS_ENGINEER',
        ]);
    }
}
