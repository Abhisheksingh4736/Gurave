<?php

namespace App\Models;

use App\Enum\ActiveStatusEnum;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Wildside\Userstamps\Userstamps;

class AssessmentHeader extends Model
{
    use HasFactory, Userstamps, SoftDeletes, LogsActivity;

    const CREATED_BY = 'created_by';
    const UPDATED_BY = 'updated_by';
    const DELETED_BY = 'deleted_by';

    protected $casts = [
        'is_active' => ActiveStatusEnum::class,
       
    ];

    protected $fillable = [
        'name', 'is_active',
        'assessment_name',
        'assessment_type',
        'assessment_duration',
        'assessment_time',
        'board_id',
        'medium_id',
        'standard_id',
        'course_id',
        'chapter_id', // Stored as comma-separated values
        'topic_id', // Stored as comma-separated values
        'assessment_level',
         'total_marks'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ActiveScope);
    }


    /**
     * Save activity log
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInActive(Builder $query): void
    {
        $query->where('is_active', ActiveStatusEnum::Inactive);
    }

    /**
     * Scope a query to exclude active scope.
     */
    public function scopeWithoutStatus(Builder $query): void
    {
        $query->withoutGlobalScope(ActiveScope::class);
    }
    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_header_id');
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
    public function medium()
    {
        return $this->belongsTo(Medium::class);
    }

    public function course()
    {
        return $this->belongsTo(CourseTemplate::class, 'course_id');
    }

    public function getChaptersAttribute()
    {
        // Check if subject_id exists and is not empty
        if (!$this->chapter_id) {
            return collect(); // Return an empty collection instead of null
        }

        // Convert the comma-separated string into an array
        $chapterIds = array_filter(explode(',', $this->chapter_id));

        // Fetch subjects only if there are valid IDs
        return Chapter::whereIn('id', $chapterIds)->get(['id', 'name']);
    }

    public function getTopicsAttribute()
    {

        // Check if subject_id exists and is not empty
            if (!$this->topic_id) {
                return collect(); // Return an empty collection instead of null
            }
    
            // Convert the comma-separated string into an array
            $topicIds = array_filter(explode(',', $this->topic_id));
    
            // Fetch subjects only if there are valid IDs
            return Topic::whereIn('id', $topicIds)->get(['id', 'name']);

    }


    public function chapters()
    {
        return $this->belongsTo(Chapter::Class, 'id','chapter_id');
    }

    // Dynamic Relationship: Topics
    public function topics()
    {
        return $this->hasMany(Topic::Class, 'id','topic_id');
    }
    
    
}
