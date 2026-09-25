<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\User;
use App\Models\Project;
use App\Models\AssetAssignment;
use App\Models\ActivityLog;
use Carbon\Carbon;

class AssetManager extends Component
{
    public bool $showCreateModal = false;
    public string $filterType = 'ALL';

    public ?int $editingAssetId = null;
    public string $name = '';
    public string $asset_code = '';
    public string $type = 'HARDWARE';
    public string $serial_spec = '';
    public string $owner = 'Solvia.Nova';
    public ?int $responsible_user_id = null;
    public string $status = 'ACTIVE';
    public float $cost = 0;
    public string $expiry_date = '';
    public ?int $project_id = null;
    public string $notes = '';

    protected $rules = [
        'name' => 'required|min:3',
        'asset_code' => 'required|min:3',
        'type' => 'required',
    ];

    public function openCreateModal()
    {
        $this->resetForm();
        $this->asset_code = 'AST-' . strtoupper(substr(md5(microtime()), 0, 5));
        $this->showCreateModal = true;
    }

    public function editAsset(int $id)
    {
        $ast = Asset::findOrFail($id);
        $this->editingAssetId = $ast->id;
        $this->name = $ast->name;
        $this->asset_code = $ast->asset_code;
        $this->type = $ast->type;
        $this->serial_spec = $ast->serial_spec ?? '';
        $this->owner = $ast->owner ?? 'Solvia.Nova';
        $this->responsible_user_id = $ast->responsible_user_id;
        $this->status = $ast->status;
        $this->cost = (float) $ast->cost;
        $this->expiry_date = $ast->expiry_date?->format('Y-m-d') ?? '';
        $this->project_id = $ast->project_id;
        $this->notes = $ast->notes ?? '';
        $this->showCreateModal = true;
    }

    public function deleteAsset(int $id)
    {
        if (!auth()->user()->isSuperAdmin()) return;

        $ast = Asset::findOrFail($id);
        $name = $ast->name;
        $ast->delete();

        ActivityLog::log('ASSET_DELETED', 'Super Admin menghapus aset "' . $name . '".', null);
        $this->dispatch('toast', message: 'Aset berhasil dihapus!', type: 'error');
    }

    public function saveAsset()
    {
        $this->validate();

        if ($this->editingAssetId) {
            $asset = Asset::findOrFail($this->editingAssetId);
            $asset->update([
                'name' => $this->name,
                'asset_code' => $this->asset_code,
                'type' => $this->type,
                'serial_spec' => $this->serial_spec,
                'owner' => $this->owner,
                'responsible_user_id' => $this->responsible_user_id,
                'status' => $this->status,
                'cost' => $this->cost,
                'expiry_date' => $this->expiry_date ?: null,
                'project_id' => $this->project_id,
                'notes' => $this->notes,
            ]);
            ActivityLog::log('ASSET_UPDATED', 'Aset "' . $this->name . '" diperbarui.', $asset);
            $this->dispatch('toast', message: 'Aset berhasil diperbarui!');
        } else {
            $asset = Asset::create([
                'name' => $this->name,
                'asset_code' => $this->asset_code,
                'type' => $this->type,
                'serial_spec' => $this->serial_spec,
                'owner' => $this->owner,
                'responsible_user_id' => $this->responsible_user_id,
                'status' => $this->status,
                'cost' => $this->cost,
                'expiry_date' => $this->expiry_date ?: null,
                'project_id' => $this->project_id,
                'notes' => $this->notes,
            ]);

            if ($this->responsible_user_id) {
                AssetAssignment::create([
                    'asset_id' => $asset->id,
                    'user_id' => $this->responsible_user_id,
                    'status' => 'ASSIGNED',
                    'assigned_date' => Carbon::today(),
                    'notes' => 'Penyerahan aset awal.',
                ]);
            }

            ActivityLog::log('ASSET_CREATED', 'Aset baru "' . $this->name . '" (' . $this->asset_code . ') dicatat.', $asset);
            $this->dispatch('toast', message: 'Aset/Infrastruktur berhasil dicatat!');
        }

        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingAssetId = null;
        $this->name = '';
        $this->asset_code = '';
        $this->type = 'HARDWARE';
        $this->serial_spec = '';
        $this->owner = 'Solvia.Nova';
        $this->responsible_user_id = null;
        $this->status = 'ACTIVE';
        $this->cost = 0;
        $this->expiry_date = '';
        $this->project_id = null;
        $this->notes = '';
    }

    public function render()
    {
        $query = Asset::with(['responsibleUser', 'project']);

        if ($this->filterType !== 'ALL') {
            $query->where('type', $this->filterType);
        }

        $assets = $query->latest()->get();
        $users = User::where('status', 'ACTIVE')->get();
        $projects = Project::where('status', 'ACTIVE')->get();

        // Expiring Soon List
        $expiringSoonAssets = Asset::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->get();

        return view('livewire.asset-manager', [
            'assets' => $assets,
            'users' => $users,
            'projects' => $projects,
            'expiringSoonAssets' => $expiringSoonAssets,
        ]);
    }
}
