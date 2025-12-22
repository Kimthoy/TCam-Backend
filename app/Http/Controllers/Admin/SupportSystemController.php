<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportSection;
use App\Models\SupportPlan;
use App\Models\SupportPlanFeature;
use App\Models\SupportOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportSystemController extends Controller
{
    // GET: fetch full support system
    public function index()
    {
        return response()->json([
            'section' => SupportSection::where('is_active', true)->first(),
            'plans'   => SupportPlan::with('features')->where('is_active', true)->orderBy('display_order')->get(),
            'options' => SupportOption::where('is_active', true)->orderBy('display_order')->get(),
        ]);
    }

    // CREATE / UPDATE
    public function store(Request $request)
    {
        $request->validate([
            'section.section_title'       => 'required|string|max:255',
            'section.section_description' => 'nullable|string',
            'section.iso_certification'   => 'nullable|string',
            'plans'                       => 'required|array|min:1',
            'plans.*.plan_name'           => 'required|string|max:100',
            'plans.*.support_hours_label' => 'required|string|max:20',
            'plans.*.support_coverage'    => 'required|string',
            'plans.*.features'            => 'required|array|min:1',
            'options'                     => 'required|array|min:1',
            'options.*.option_title'      => 'required|string|max:255',
            'options.*.option_description'=> 'required|string',
        ]);

        DB::transaction(function () use ($request) {

            // ------------------ SECTION ------------------
            $section = SupportSection::first();
            if ($section) {
                $section->update([
                    'section_title'       => $request->section['section_title'],
                    'section_description' => $request->section['section_description'] ?? null,
                    'iso_certification'   => $request->section['iso_certification'] ?? null,
                    'is_active'           => true,
                ]);
            } else {
                $section = SupportSection::create([
                    'section_title'       => $request->section['section_title'],
                    'section_description' => $request->section['section_description'] ?? null,
                    'iso_certification'   => $request->section['iso_certification'] ?? null,
                    'is_active'           => true,
                ]);
            }

            // ------------------ PLANS ------------------
            $existingPlanIds = SupportPlan::pluck('id')->toArray();
            $incomingPlanIds = array_column($request->plans, 'id');
            $toDeletePlans = array_diff($existingPlanIds, $incomingPlanIds);
            SupportPlan::whereIn('id', $toDeletePlans)->delete();

            foreach ($request->plans as $planData) {
                if (!empty($planData['id'])) {
                    // Update existing plan
                    $plan = SupportPlan::find($planData['id']);
                    $plan->update([
                        'plan_name'                       => $planData['plan_name'],
                        'badge_color'                     => $planData['badge_color'] ?? null,
                        'support_hours_label'             => $planData['support_hours_label'],
                        'support_coverage'                => $planData['support_coverage'],
                        'include_holidays'                => $planData['include_holidays'] ?? false,
                        'exclude_holidays'                => $planData['exclude_holidays'] ?? false,
                        'preventive_maintenance_per_year' => $planData['preventive_maintenance_per_year'] ?? 0,
                        'case_support'                    => $planData['case_support'] ?? 'Unlimited case support',
                        'cta_text'                        => $planData['cta_text'] ?? 'Contact Now',
                        'display_order'                   => $planData['display_order'] ?? 0,
                        'is_active'                       => true,
                    ]);
                } else {
                    // Create new plan
                    $plan = SupportPlan::create([
                        'plan_name'                       => $planData['plan_name'],
                        'badge_color'                     => $planData['badge_color'] ?? null,
                        'support_hours_label'             => $planData['support_hours_label'],
                        'support_coverage'                => $planData['support_coverage'],
                        'include_holidays'                => $planData['include_holidays'] ?? false,
                        'exclude_holidays'                => $planData['exclude_holidays'] ?? false,
                        'preventive_maintenance_per_year' => $planData['preventive_maintenance_per_year'] ?? 0,
                        'case_support'                    => $planData['case_support'] ?? 'Unlimited case support',
                        'cta_text'                        => $planData['cta_text'] ?? 'Contact Now',
                        'display_order'                   => $planData['display_order'] ?? 0,
                        'is_active'                       => true,
                    ]);
                }

                // ------------------ FEATURES ------------------
                $existingFeatureIds = $plan->features()->pluck('id')->toArray();
                $incomingFeatureIds = array_column($planData['features'], 'id');
                $toDeleteFeatures = array_diff($existingFeatureIds, $incomingFeatureIds);
                SupportPlanFeature::whereIn('id', $toDeleteFeatures)->delete();

                foreach ($planData['features'] as $feature) {
                    if (!empty($feature['id'])) {
                        $plan->features()->where('id', $feature['id'])->update([
                            'feature_text'   => $feature['feature_text'],
                            'is_highlighted' => $feature['is_highlighted'] ?? false,
                        ]);
                    } else {
                        $plan->features()->create([
                            'feature_text'   => $feature['feature_text'],
                            'is_highlighted' => $feature['is_highlighted'] ?? false,
                        ]);
                    }
                }
            }

            // ------------------ OPTIONS ------------------
            $existingOptionIds = SupportOption::pluck('id')->toArray();
            $incomingOptionIds = array_column($request->options, 'id');
            $toDeleteOptions = array_diff($existingOptionIds, $incomingOptionIds);
            SupportOption::whereIn('id', $toDeleteOptions)->delete();

            foreach ($request->options as $option) {
                if (!empty($option['id'])) {
                    SupportOption::find($option['id'])->update([
                        'option_title'       => $option['option_title'],
                        'option_description' => $option['option_description'],
                        'display_order'      => $option['display_order'] ?? 0,
                        'is_active'          => true,
                    ]);
                } else {
                    SupportOption::create([
                        'option_title'       => $option['option_title'],
                        'option_description' => $option['option_description'],
                        'display_order'      => $option['display_order'] ?? 0,
                        'is_active'          => true,
                    ]);
                }
            }
        });

        return response()->json([
            'status'  => true,
            'message' => 'Support system updated successfully',
        ]);
    }

    // DELETE individual plan
    public function destroyPlan($id)
    {
        SupportPlan::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Plan deleted']);
    }

    // DELETE individual option
    public function destroyOption($id)
    {
        SupportOption::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Option deleted']);
    }

    // DELETE individual feature
    public function destroyFeature($id)
    {
        SupportPlanFeature::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Feature deleted']);
    }
}
