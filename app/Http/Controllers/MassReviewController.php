<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MassReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userRole = strtoupper($user->role ?? '');

        $query = Form::with([
            'workPlans',
            'financialPlans',
        ]);

        $query->whereHas('workPlans', function ($q) {
            $q->whereIn('status', [
                'For Reviewal',
                'FOR REVISION',
                'Pending',
                'For Submission to Finance',
            ]);
        });

        if ($userRole === 'DEPARTMENT MANAGER') {
            $userRC = $user->responsibility_center ?? '';
            $userDept = strtoupper($user->operating_department ?? '');

            $ogmGroupRCs = [
                'OGM',
                'OAGM',
                'SMO',
                'PIU',
                'IAD',
                'LAD',
                'PPIMD',
            ];

            if ($userDept === 'OGM') {
                $query->where(function ($q) use ($userRC, $ogmGroupRCs) {
                    $q->whereHas('workPlans', function ($wp) use ($userRC) {
                        $wp->where('r_center', $userRC);
                    })->orWhereHas('workPlans', function ($wp) use ($ogmGroupRCs) {
                        $wp->whereIn('r_center', $ogmGroupRCs);
                    });
                });
            } else {
                $query->whereHas('workPlans', function ($wp) use ($userRC) {
                    $wp->where('r_center', $userRC);
                });
            }
        }

        if ($userRole === 'FINANCE') {
            $query->whereHas('workPlans', function ($q) {
                $q->where('status', 'For Submission to Finance');
            });
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('r_center')) {
            $query->whereHas('workPlans', function ($q) use ($request) {
                $q->where('r_center', $request->r_center);
            });
        }

        $forms = $query
            ->orderByDesc('created_at')
            ->get();

        $rows = [];

        foreach ($forms as $form) {
            foreach ($form->workPlans as $workPlan) {
                $financialPlans = $form->financialPlans
                    ->where('workplan_id', $workPlan->id)
                    ->values();

                if ($financialPlans->isEmpty()) {
                    $rows[] = $this->buildRow($form, $workPlan);
                    continue;
                }

                foreach ($financialPlans as $financialPlan) {
                    $rows[] = $this->buildRow(
                        $form,
                        $workPlan,
                        $financialPlan
                    );
                }
            }
        }

        $rows = collect($rows)
            ->sortBy([
                ['form_id', 'asc'],
                ['workplan_id', 'asc'],
                ['financialplan_id', 'asc'],
            ])
            ->values()
            ->all();

        $years = collect($rows)
            ->pluck('year')
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $responsibilityCenters = collect($rows)
            ->pluck('r_center')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('mass-review.index', compact(
            'rows',
            'years',
            'responsibilityCenters',
            'userRole'
        ));
    }

    private function buildRow($form, $workPlan, $financialPlan = null)
    {
        return [
            'form_id' => $form->id,
            'form_ref' => $form->form_ref,
            'year' => $form->year,
            'created_at' => $form->created_at,

            'workplan_id' => $workPlan->id,
            'status' => $workPlan->status,
            'r_center' => $workPlan->r_center,
            'department' => $workPlan->department,

            'strategic_perspective' => $workPlan->strategic_perspective,
            'strategic_objective' => $workPlan->strategic_objective,
            'major_program' => $workPlan->major_program,
            'strategic_measure' => $workPlan->strategic_measure,
            'strategic_initiatives' => $workPlan->strategic_initiatives,
            'success_indicator' => $workPlan->success_indicator,

            'wp_q1' => $workPlan->q1,
            'wp_q2' => $workPlan->q2,
            'wp_q3' => $workPlan->q3,
            'wp_q4' => $workPlan->q4,
            'wp_total' => $workPlan->total,
            'remarks' => $workPlan->remarks,

            'financialplan_id' => $financialPlan?->id,

            'funds' => $financialPlan?->funds,
            'programs' => $financialPlan?->programs,
            'projects' => $financialPlan?->projects,
            'activity' => $financialPlan?->activity,
            'description' => $financialPlan?->description,
            'expense_class' => $financialPlan?->expense_class,
            'account_title' => $financialPlan?->account_title,

            'fp_q1' => $financialPlan?->q1,
            'fp_q2' => $financialPlan?->q2,
            'fp_q3' => $financialPlan?->q3,
            'fp_q4' => $financialPlan?->q4,
            'fp_total' => $financialPlan?->total,
        ];
    }

    public function forReviewal(Request $request)
    {
        $request->validate([
            'form_ids' => ['required', 'array'],
            'form_ids.*' => ['integer'],
        ]);

        DB::table('workplan')
            ->whereIn('form_id', $request->form_ids)
            ->update([
                'status' => 'For Reviewal',
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Selected plans sent for reviewal.',
        ]);
    }

    public function approve(Request $request)
    {
        $request->validate([
            'form_ids' => ['required', 'array'],
            'form_ids.*' => ['integer'],
        ]);

        DB::table('workplan')
            ->whereIn('form_id', $request->form_ids)
            ->update([
                'status' => 'Approved',
                'updated_at' => now(),
            ]);

        Form::whereIn('id', $request->form_ids)
            ->update([
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Selected plans approved successfully.',
        ]);
    }

    public function revise(Request $request)
    {
        $request->validate([
            'form_ids' => ['required', 'array'],
            'form_ids.*' => ['integer'],
        ]);

        DB::table('workplan')
            ->whereIn('form_id', $request->form_ids)
            ->update([
                'status' => 'FOR REVISION',
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Selected plans sent for revision.',
        ]);
    }

    public function submitToFinance(Request $request)
    {
        $request->validate([
            'form_ids' => ['required', 'array'],
            'form_ids.*' => ['integer'],
        ]);

        DB::table('workplan')
            ->whereIn('form_id', $request->form_ids)
            ->update([
                'status' => 'For Submission to Finance',
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Selected plans submitted to Finance.',
        ]);
    }
}