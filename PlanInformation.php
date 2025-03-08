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

class PlanInformation extends Model
{
    use HasFactory, Userstamps, SoftDeletes, LogsActivity;

    const CREATED_BY = 'created_by';
    const UPDATED_BY = 'updated_by';
    const DELETED_BY = 'deleted_by';

    protected $table ='plan_information';
    
    protected $casts = [
        'is_active' => ActiveStatusEnum::class,
    ];

    protected $fillable = [
            'name', 'applicable_type', 'currency', 'status', 'popular', 'display_order', 'upload_file',
            'duration', 'amount','full_view_access_to_all_courses', 'full_create_own_courses', 'full_generate_custom_output',
            'full_download_as_Doc_PPT', 'full_share_courses_to_other_users', 'full_course_edit_rights_to_multiple_users',
            'full_custom_portal', 'full_view_access_limit', 'full_view_access_text',
            'full_image2text_limit', 'full_image2text_text', 'full_tokens_limit', 'full_tokens_text',
            'full_pdf_upload_limit', 'full_pdf_upload_text',
            'trial_available', 'trial_days', 'trial_view_access_to_all_courses', 'trial_create_own_courses',
            'trial_generate_custom_output', 'trial_download_as_Doc_PPT', 'trial_share_courses_to_other_users',
            'trial_course_edit_rights_to_multiple_users', 'trial_custom_portal',
            'trial_full_access_limit', 'trial_image2text_limit', 'trial_tokens_limit', 'trial_pdf_upload_limit','is_active'
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
}
