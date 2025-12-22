<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportPlanFeature extends Model
{
    protected $table = 'support_plan_features';

    protected $fillable = [
        'support_plan_id',
        'feature_text',
        'is_highlighted',
    ];

    /**
     * Feature belongs to a support plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SupportPlan::class, 'support_plan_id');
    }
}
