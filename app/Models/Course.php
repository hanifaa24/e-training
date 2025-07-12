<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;

class Course extends Model
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
    protected $fillable = ['name', 'description', 'subject_id', 'image', 'is_hidden', 'publish', 'created_by', 'updated_by'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('resource') // kategori log
            ->dontSubmitEmptyLogs();
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image);
    }
    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function courseProgress()
    {
        return $this->hasOne(CourseProgress::class)->where('user_id', auth()->id());
    }

    public function quizScore()
    {
        return $this->hasOne(QuizScore::class)->where('user_id', auth()->id());
    }

    protected $casts = [
        'is_hidden' => 'boolean',
    ];
}
