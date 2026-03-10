<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternshipOpportunity;
use App\Models\User;

class JobController extends Controller
{

    //الشركة تنزل وظيفة او تدريب
    public function createJob(Request $request)
    {
        if ($request->user()->role !== 'company') {
            return response()->json([
                'status' => false,
                'message' => 'فقط حساب الشركة يمكنه إضافة وظائف'
            ], 403);
        }

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'required|string', // text
            'type'             => 'required|in:training,job',
            'field'            => 'nullable|string|max:255',
            'city'             => 'nullable|string|max:255',
            'work_type'        => 'nullable|in:onsite,remote,hybrid',
            'requirements'     => 'nullable|string',
            'education_level'  => 'nullable|string|max:255',
            'duration'         => 'nullable|integer|min:1',
            'deadline'         => 'nullable|date',
            'status'           => 'nullable|in:open,closed',
        ]);


        $job = InternshipOpportunity::create([
            'company_user_id'  => $request->user()->id,
            'title'            => $validated['title'],
            'description'      => $validated['description'],
            'type'             => $validated['type'],

            'field'            => $validated['field'] ?? null,
            'city'             => $validated['city'] ?? null,
            'work_type'        => $validated['work_type'] ?? null,

            'requirements'     => $validated['requirements'] ?? null,
            'education_level'  => $validated['education_level'] ?? null,

            'duration'         => $validated['duration'] ?? null,
            'deadline'         => $validated['deadline'] ?? null,

            'status'           => $validated['status'] ?? 'open',
        ]);

        return response()->json([
            'status' => true,
            'data'   => $job,
        ], 201);
    }

    public function updateJob(Request $request, $id)
    {
        if ($request->user()->role !== 'company') {
            return response()->json([
                'status' => false,
                'message' => 'فقط حساب الشركة يمكنه تعديل الوظائف'
            ], 403);
        }

        $job = InternshipOpportunity::find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'الوظيفة غير موجودة'
            ], 404);
        }

        // تأكد إنها نفس الشركة اللي أنشأت الوظيفة
        if ($job->company_user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'غير مسموح لك تعديل هذه الوظيفة'
            ], 403);
        }

        $validated = $request->validate([
            'title'            => 'sometimes|required|string|max:255',
            'description'      => 'sometimes|required|string',
            'type'             => 'sometimes|required|in:training,job',

            'field'            => 'nullable|string|max:255',
            'city'             => 'nullable|string|max:255',
            'work_type'        => 'nullable|in:onsite,remote,hybrid',

            'requirements'     => 'nullable|string',
            'education_level'  => 'nullable|string|max:255',

            'duration'         => 'nullable|integer|min:1',
            'deadline'         => 'nullable|date',

            'status'           => 'nullable|in:open,closed',
        ]);

        $job->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'تم تعديل الوظيفة بنجاح',
            'data' => $job
        ]);
    }

    public function deleteJob(Request $request, $id)
    {
        if ($request->user()->role !== 'company') {
            return response()->json([
                'status' => false,
                'message' => 'فقط حساب الشركة يمكنه حذف الوظائف'
            ], 403);
        }

        $job = InternshipOpportunity::find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'الوظيفة غير موجودة'
            ], 404);
        }

        // تأكد إنها نفس الشركة اللي أنشأت الوظيفة
        if ($job->company_user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'غير مسموح لك حذف هذه الوظيفة'
            ], 403);
        }

        $job->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف الوظيفة بنجاح'
        ]);
    }
}
