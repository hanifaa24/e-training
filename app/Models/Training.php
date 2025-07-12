<?php

namespace App\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Training extends Model
{
    use LogsActivity;

    protected $fillable = ['title', 'file', 'link', 'course_id', 'time', 'date', 'original_filename', 'employee_id', 'status', 'created_by', 'updated_by'];
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

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getViewData(): array
    {
        $employee = Auth::user()?->employee;

        $upcomingTraining = null;

        if ($employee) {
            $upcomingTraining = \App\Models\Training::whereJsonContains('employee_id', (string) $employee->id)
                ->where('date', '>', now())
                ->orderBy('date')
                ->first();
        }

        return [
            'upcomingTraining' => $upcomingTraining,
        ];
    }
    protected $casts = [
        'employee_id' => 'array',
    ];
}

