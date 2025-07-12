<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Role extends SpatieRole
{
    use LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('resource') 
            ->dontSubmitEmptyLogs();
    }

    public function users(): MorphToMany
{
    return $this->morphedByMany(User::class, 'model', 'model_has_roles', 'role_id');
}
    
}
