<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->logAuditAction('CREATE');
        });

        static::updated(function (Model $model) {
            $model->logAuditAction('UPDATE');
        });

        static::deleted(function (Model $model) {
            $model->logAuditAction('DELETE');
        });
    }

    /**
     * Log the audit action.
     *
     * @param string $action
     */
    protected function logAuditAction(string $action)
    {
        $beforeData = null;
        $afterData = null;

        if ($action === 'CREATE') {
            $afterData = $this->getAttributes();
        } elseif ($action === 'UPDATE') {
            // Get only the changed attributes
            $afterData = $this->getChanges();
            // Get original attributes for the changed keys
            $beforeData = array_intersect_key($this->getOriginal(), $afterData);
            
            // If no actual changes (e.g., only timestamps), don't log
            if (empty($afterData)) {
                return;
            }
        } elseif ($action === 'DELETE') {
            $beforeData = $this->getOriginal();
        }

        // Get the module name from the model property if it exists, otherwise use the class name
        $module = property_exists($this, 'auditModule') 
            ? $this->auditModule 
            : class_basename($this);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'entity_type' => get_class($this),
            'entity_id' => $this->id,
            'before_data' => $beforeData,
            'after_data' => $afterData,
            'ip_address' => request()->ip(),
        ]);
    }
}
