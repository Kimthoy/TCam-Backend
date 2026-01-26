<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SupportSection;
use App\Models\SupportPlan;
use App\Models\SupportPlanFeature;
use App\Models\SupportOption;

class SupportSystemController extends Controller
{
    // LIST
    public function index()
    {
        $section = SupportSection::first();
        $plans = SupportPlan::with('features')->orderBy('display_order')->get();
        $options = SupportOption::orderBy('display_order')->get();

        return response()->json([
            'section' => $section,
            'plans' => $plans,
            'options' => $options,
        ]);
    }

    // GET BY ID
    public function show($id)
    {
        $section = SupportSection::first();
        $plan = SupportPlan::with('features')->findOrFail($id);
        $options = SupportOption::orderBy('display_order')->get();

        return response()->json([
            'section' => $section,
            'plan' => $plan,
            'options' => $options,
        ]);
    }

    // CREATE / UPDATE (works for both)
    public function store(Request $request)
    {
        return $this->saveSupport($request);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        return $this->saveSupport($request, $id);
    }

    // DELETE ALL
    public function destroy($id)
    {
        SupportSection::where('id', $id)->delete();
        SupportPlan::where('support_section_id', $id)->delete();
        SupportOption::where('support_section_id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Support system deleted',
        ]);
    }

    // DELETE PLAN
    public function destroyPlan($id)
    {
        SupportPlan::where('id', $id)->delete();
        SupportPlanFeature::where('support_plan_id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Plan deleted',
        ]);
    }

    // DELETE OPTION
    public function destroyOption($id)
    {
        SupportOption::where('id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Option deleted',
        ]);
    }

    // DELETE FEATURE
    public function destroyFeature($id)
    {
        SupportPlanFeature::where('id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Feature deleted',
        ]);
    }

    // COMMON SAVE FUNCTION
    private function saveSupport(Request $request, $id = null)
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

            // SECTION
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

            // PLANS
            $existingPlanIds = SupportPlan::pluck('id')->toArray();
            $incomingPlanIds = array_column($request->plans, 'id');
            $toDeletePlans = array_diff($existingPlanIds, $incomingPlanIds);
            SupportPlan::whereIn('id', $toDeletePlans)->delete();

            foreach ($request->plans as $planData) {
                if (!empty($planData['id'])) {
                    $plan = SupportPlan::find($planData['id']);
                    $plan->update([
                        'plan_name'            => $planData['plan_name'],
                        'badge_color'          => $planData['badge_color'] ?? null,
                        'support_hours_label'  => $planData['support_hours_label'],
                        'support_coverage'     => $planData['support_coverage'],
                        'include_holidays'     => $planData['include_holidays'] ?? false,
                        'exclude_holidays'     => $planData['exclude_holidays'] ?? false,
                        'preventive_maintenance_per_year' => $planData['preventive_maintenance_per_year'] ?? 0,
                        'case_support'         => $planData['case_support'] ?? 'Unlimited case support',
                        'cta_text'             => $planData['cta_text'] ?? 'Contact Now',
                        'display_order'        => $planData['display_order'] ?? 0,
                        'is_active'            => true,
                    ]);
                } else {
                    $plan = SupportPlan::create([
                        'plan_name'            => $planData['plan_name'],
                        'badge_color'          => $planData['badge_color'] ?? null,
                        'support_hours_label'  => $planData['support_hours_label'],
                        'support_coverage'     => $planData['support_coverage'],
                        'include_holidays'     => $planData['include_holidays'] ?? false,
                        'exclude_holidays'     => $planData['exclude_holidays'] ?? false,
                        'preventive_maintenance_per_year' => $planData['preventive_maintenance_per_year'] ?? 0,
                        'case_support'         => $planData['case_support'] ?? 'Unlimited case support',
                        'cta_text'             => $planData['cta_text'] ?? 'Contact Now',
                        'display_order'        => $planData['display_order'] ?? 0,
                        'is_active'            => true,
                    ]);
                }

                // FEATURES
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

            // OPTIONS
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
            'status' => true,
            'message' => 'Support system updated successfully',
        ]);
    }
}
