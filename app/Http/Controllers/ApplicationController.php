<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\User;

use Illuminate\Http\Request;

class ApplicationController extends Controller
{

    //الطالب يقدم طلب للوظيفة الي الشركة نزلتها 
    public function applyApplication(Request $request)
    {
        if ($request->user()->role !== 'student') {
            return response()->json([
                'status' => false,
                'message' => 'فقط حساب الطالب يمكنه إضافة الطلب'
            ], 403);
        }

        $validated = $request->validate([
            'opportunity_id' => 'required|exists:internship_opportunities,id',
            'skills' => 'nullable|string',
            'motivation' => 'nullable|string|max:500',
            'cv' => 'required|file|mimes:pdf'
        ]);

        $cvPath = $request->file('cv')->store('cvs', 'public');

        $application = Application::create([
            'student_id' => $request->user()->id,
            'opportunity_id' => $validated['opportunity_id'],
            'skills' => $validated['skills'] ?? null,
            'motivation' => $validated['motivation'] ?? null,
            'cv' => $cvPath,
            'company_status' => 'pending',
            'supervisor_status' => 'pending',
            'final_status' => 'pending'
        ]);

        return response()->json([
            'status' => true,
            'data' => $application
        ], 201);
    }


    private function updateFinalStatus($application)
    {
        if (
            $application->company_status === 'approved' &&
            $application->supervisor_status === 'approved'
        ) {
            $application->final_status = 'approved';
            $application->approved_at = now();
        } elseif (
            $application->company_status === 'rejected' ||
            $application->supervisor_status === 'rejected'
        ) {
            $application->final_status = 'rejected';
        } else {
            $application->final_status = 'pending';
        }
    }


    //الشركة تقبل طلب الطالب الي قدم عليها 
    public function companyApplicationApprove(Request $request, $id)
    {
        if ($request->user()->role !== 'company') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application = Application::findOrFail($id);

        $application->company_status = 'approved';

        $this->updateFinalStatus($application);

        $application->save();

        return response()->json([
            'status' => true,
            'data' => $application
        ]);
    }

    //الشركة ترفض طلب الطالب الي قدم عليها 
    public function companyApplicationReject(Request $request, $id)
    {
        if ($request->user()->role !== 'company') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application = Application::findOrFail($id);

        $application->company_status = 'rejected';
        $application->final_status = 'rejected';

        $application->save();

        return response()->json([
            'status' => true,
            'data' => $application
        ]);
    }

    //المشرف يقبل طلب الطالب الي قدم عليها 
    public function supervisorApplicationApprove(Request $request, $id)
    {
        if ($request->user()->role !== 'supervisor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application = Application::findOrFail($id);

        $application->supervisor_status = 'approved';

        $this->updateFinalStatus($application);

        $application->save();

        return response()->json([
            'status' => true,
            'data' => $application
        ]);
    }

    //المشرف ترفض طلب الطالب الي قدم عليها 
    public function supervisorReject(Request $request, $id)
    {
        if ($request->user()->role !== 'supervisor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application = Application::findOrFail($id);

        $application->supervisor_status = 'rejected';
        $application->final_status = 'rejected';

        $application->save();

        return response()->json([
            'status' => true,
            'data' => $application
        ]);
    }

    // الطلبات على الوظائف الي قدم عليها الطالب عرضهم
    public function myApplications(Request $request)
    {
        $applications = Application::where('student_id', $request->user()->id)
            ->with('opportunity')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $applications
        ]);
    }

    // عرض الطلبات الي قدموها الطلاب على وظيفة الشركة 
    public function companyApplications(Request $request)
    {
        if ($request->user()->role !== 'company') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        //الفرص التابعة للركة هاي
        $applications = Application::whereHas('opportunity', function ($q) use ($request) {
            $q->where('company_user_id', $request->user()->id);
        })
            ->with(['student', 'opportunity'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $applications
        ]);
    }

    // عرضت طلبات الطلاب للمشرف ليشوفها 
    public function supervisorApplications(Request $request)
    {
        if ($request->user()->role !== 'supervisor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $applications = Application::with(['student', 'opportunity'])->get();

        return response()->json([
            'status' => true,
            'data' => $applications
        ]);
    }

    // الطلاب التابعين للمشرف 
    public function supervisorStudents(Request $request)
    {
        if ($request->user()->role !== 'supervisor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // اختار الأعمدة المطلوبة فقط لتجنب lazy load errors
        $students = User::where('role', 'student')
            ->where('supervisor_code', $request->user()->supervisor_code)
            ->get(['id', 'name', 'email', 'status', 'supervisor_code']);

        return response()->json([
            'status' => true,
            'data' => $students
        ]);
    }

    public function supervisorActiveStudentAcouunt(Request $request, $id)
    {
        // تأكد أن المستخدم مشرف
        if ($request->user()->role !== 'supervisor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // جلب الطالب والتأكد أنه student
        $student = User::where('id', $id)->where('role', 'student')->firstOrFail();

        // تحقق أن الطالب تابع للمشرف
        if ($student->supervisor_code !== $request->user()->supervisor_code) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكنك الموافقة على طالب ليس من طلابك'
            ], 403);
        }

         $student->status = 'active';  
        $student->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تفعيل حساب الطالب بنجاح',
            'data' => $student->only(['id', 'name', 'email', 'status', 'supervisor_code'])
        ], 200);
    }

    public function rejectActiveStudentAcouunt(Request $request, $id)
    {
        if ($request->user()->role !== 'supervisor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student = User::findOrFail($id);

        $student->status = 'rejected';

        $student->save();

        return response()->json([
            'status' => true,
            'message' => 'Student rejected'
        ]);
    }
}
