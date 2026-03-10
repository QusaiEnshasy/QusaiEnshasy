<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{

    // لقبول و رفض مشرف من صفحة الادمن 
    public function updateSupervisorStatus(Request $request, $id)
    {
        $supervisor = User::where('id', $id)->where('role', 'supervisor')->firstOrFail();

        $request->validate([
            'action' => 'required|in:active,reject,delete'
        ]);

        if ($request->action === 'active') {
            $supervisor->status = 'active';
            $supervisor->save();
            return response()->json([
                'status' => true,
                'message' => "تم تفعيل حساب المشرف"

            ], 200);
        } elseif ($request->action === 'reject') {
            $delet = User::where("id", $id)->where("role", "supervisor")->firstOrFail();
            $delet->status = "rejected";
            $delet->save();

            return response()->json([
                'status' => true,
                'message' => "تم تعطيل حساب المشرف"

            ], 200);
        } elseif ($request->action === 'delete') {
            $delete = User::where('id', $id)->where('role', 'supervisor')->firstOrFail();


            $delete->delete();
            $supervisor->status = 'delete';
            $supervisor->save();
            return response()->json([
                'status' => true,
                'message' => "تم حدف حساب المشرف"

            ], 200);
        }
    }

 
    // لقبول و رفض شركة من صفحة الادمن 
    public function updateCompanyStatus(Request $request, $id)
    {


        $active = User::where('id', $id)->where('role', 'company')->firstOrFail();;
        $request->validate([
            'action' => 'required|in:active,reject,delete'
        ]);


        if ($request->action === "active") {
            $active->status = "active";
            $active->save();
            return response()->json([


                'status' => true,
                'message' => "تم تفعيل حساب الشركة"

            ], 200);
        } elseif ($request->action === "reject") {
            $reject = User::where('id', $id)->where('role', 'company')->firstOrFail();

            $reject->status = ("reject");
            $reject->save();
            return response()->json([

                'status' => true,
                'message' => "تم تعطيل الشركة"

            ], 200);
        } elseif ($request->action === "delete") {
            $delete = User::where('id', $id)->where('role', 'company')->firstOrFail();
            $delete->delete();
            return response()->json([

                'status' => true,
                'message' => "تم حدف الشركة"

            ], 200);
        }
    }
}
