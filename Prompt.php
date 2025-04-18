<?php

namespace App\Models;

use App\Enum\ActiveStatusEnum;
use App\Enum\PriceAvailabilityEnum;
use App\Enum\PromptCustomInputEnum;
use App\Enum\PromptOpenAIVersionEnum;
use App\Enum\PromptOutputTypeEnum;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Wildside\Userstamps\Userstamps;


class Prompt extends Model implements HasMedia
{
    use HasFactory, Userstamps, SoftDeletes, LogsActivity, InteractsWithMedia;

    const CREATED_BY = 'created_by';
    const UPDATED_BY = 'updated_by';
    const DELETED_BY = 'deleted_by';

    protected $casts = [
        'is_active' => ActiveStatusEnum::class,
        'output_type' => PromptOutputTypeEnum::class,
        'open_ai_version' => PromptOpenAIVersionEnum::class,
        'custom_input' => PromptCustomInputEnum::class,
        'price_availability' => PriceAvailabilityEnum::class,
    ];

    protected $fillable = [
        'name',
        'input_value',
        'description',
        'output_type',
        'category_id',
        'tag_id',
        'open_ai_version',
        'display_order',
        'is_active',
        'single_input_label',
        'single_input_placeholder',
        'multi_input_label',
        'multi_input_placeholder',
        'dropdown_label',
        'prompt_user_input',
        'custom_input',
        'gtm_tracking_id',
        'price_availability',
        'index_name',
        'help_text',
        'default_output',
        'meta_title',
        'meta_description',
        'math_input',
        'append_mapped_entity_content',
        'app_module',
        'app_submodule',
        'uploaded_files',
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

    public function alltags()
    {
        return $this->hasMany(PromptTags::class, 'prompt_id');
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

    /**
     * Get the category that owns the prompts.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withoutStatus();
    }

    public function categoryss(): BelongsTo
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }
    /**
     * The roles that belong to the user.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'prompt_tags')->withoutStatus();
    }

    /**
     * Get all of the subject that are assigned this prompt.
     */
    public function subjects(): MorphToMany
    {
        return $this->morphedByMany(Subject::class, 'prompt_mappable', 'prompt_mappings', 'prompt_id', 'prompt_mappable_id');
    }

    /**
     * Get all of the chapters that are assigned this prompt.
     */
    public function chapters(): MorphToMany
    {
        return $this->morphedByMany(Chapter::class, 'prompt_mappable', 'prompt_mappings', 'prompt_id', 'prompt_mappable_id');
    }

    /**
     * Get all of the topics that are assigned this prompt.
     */
    public function topics(): MorphToMany
    {
        return $this->morphedByMany(Topic::class, 'prompt_mappable', 'prompt_mappings', 'prompt_id', 'prompt_mappable_id');
    }

    public function prompt_mapping(): HasMany
    {
        return $this->hasMany(PromptMapping::class);
    }

    public function promptMapping()
    {
        return $this->hasMany(PromptMapping::class, 'prompt_id');
    }

    public function prompt_dropdown_values(): HasMany
    {
        return $this->hasMany(PromptDropdownValues::class)->orderBy('display_order', 'ASC');
    }
    
}
