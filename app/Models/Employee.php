<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'phone_number', 'status', 'recruitment_date', 'created_by', 'updated_by'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('resource') // kategori log
            ->dontSubmitEmptyLogs();
            //->logEvents(['created', 'updated', 'deleted']);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function userAcc()
    {
        return $this->hasOne(User::class);
    }
}
