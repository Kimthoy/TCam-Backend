<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportPlan extends Model
{
    protected $table = 'support_plans';

    protected $fillable = [
        'plan_name',
        'badge_color',
        'support_hours_label',
        'support_coverage',
        'include_holidays',
        'exclude_holidays',
        'preventive_maintenance_per_year',
        'case_support',
        'cta_text',
        'display_order',
        'is_active',
    ];

    /**
     * Plan has many features
     */
    public function features(): HasMany
    {
        return $this->hasMany(SupportPlanFeature::class, 'support_plan_id');
    }
}
