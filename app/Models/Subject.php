<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;

class Subject extends Model
{
    use LogsActivity;
    protected static bool $logEnabled = true;
    public static function disableActivityLog()
    {
        static::$logEnabled = false;
    }

    public static function enableActivityLog()
    {
        static::$logEnabled = true;
    }
    protected $fillable = ['name', 'description', 'is_hidden', 'created_by', 'updated_by'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('resource') // kategori log
            ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Subject has been {$eventName}";
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
    protected static function booted()
    {
        static::addGlobalScope('visible', function (Builder $builder) {
            $builder->where('is_hidden', false);
        });
    }
    protected $casts = [
        'is_hidden' => 'boolean',
    ];
}
