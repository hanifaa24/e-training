<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
class Material extends Model
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

    protected $fillable = ['chapter_title', 'description', 'order', 'content', 'course_id', 'is_hidden', 'publish', 'created_by', 'updated_by'];
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

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function courseProgress()
    {
        return $this->hasOne(CourseProgress::class)->where('user_id', auth()->id());
    }

    public function quizScore()
    {
        return $this->hasOne(QuizScore::class)->where('user_id', auth()->id());
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
