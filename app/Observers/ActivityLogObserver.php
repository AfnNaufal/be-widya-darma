<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    public function created(Model $model): void
    {
        $this->record('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->record('updated', $model);
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model);
    }

    /**
     * Catat aksi hanya jika ada admin yang sedang login (seeder/console dilewati).
     */
    protected function record(string $action, Model $model): void
    {
        $admin = request()->user();

        if (! $admin instanceof Admin) {
            return;
        }

        ActivityLog::create([
            'admin_id' => $admin->admin_id,
            'action' => $action,
            'target_table' => $model->getTable(),
            'target_id' => $model->getKey(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
