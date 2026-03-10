<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // بجيب جميع المستخدمين
    public function getAllUsers()
    {
        return response()->json([
            'status' => true,
            'data' => User::all()
        ]);
    }

    // =========================
    // تسجيل الدخول----
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'password'   => 'required'
        ]);

        // أدمن ثابت
        $adminEmail = 'admin';
        $adminPassword = 'admin123';

        if ($request->identifier === $adminEmail && $request->password === $adminPassword) {

            $admin = User::where('email', $adminEmail)->first();

            if (!$admin) {
                $admin = User::create([
                    'name' => 'Admin',
                    'email' => $adminEmail,
                    'role' => 'admin',
                    'password' => Hash::make($adminPassword),
                    'status' => 'active'
                ]);
            }

            Auth::login($admin);
            $token = $admin->createToken('login-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Admin login successful',
                'token' => $token,
                'data' => $admin
            ]);
        }

        // دخول عادي
        $field = filter_var($request->identifier, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'university_id';

        $user = User::where($field, $request->identifier)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'بيانات الدخول غير صحيحة'
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'حسابك قيد المراجعة'
            ]);
        }

        Auth::login($user);
        $token = $user->createToken('register-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
              'token' => $token,
            'data' => $user
        ]);
    }

    // =========================
    // تسجيل مستخدم جديد  
    // =========================
    public function register(Request $request)
    {
        // ---------- Validation أساسي ----------
        $rules = [
            'role' => 'required|in:student,supervisor,company,admin',
            'name' => 'required|string|max:255',
            'password' => 'required|confirmed|min:6',
            'phone_number' => 'required|string|min:9',
            'email' => 'nullable|email|unique:users,email',
        ];

        // ---------- حسب الدور ----------
        if ($request->role === 'student') {
            $rules['university_id'] = 'required|string|unique:users,university_id';
            $rules['supervisor_code'] = 'required|exists:users,supervisor_code';
        }

        if ($request->role === 'supervisor') {
            $rules['university_id'] = 'required|string|unique:users,university_id';
        }

        if ($request->role === 'company') {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['company_name'] = 'required|string|max:255';
            $rules['company_address'] = 'required|string|max:255';
        }

        if ($request->role === 'admin') {
            $rules['email'] = 'required|email|unique:users,email';
        }

        $request->validate($rules);

        
        // ---------- منطق السوبرفايزر ----------
        $supervisorCode = null;

        if ($request->role === 'supervisor') {
            $supervisorCode = $this->generateSupervisorCode();
        }

        if ($request->role === 'student') {
            $supervisor = User::where('supervisor_code', $request->supervisor_code)
                              ->where('role', 'supervisor')
                              ->first();

            if (!$supervisor) {
                return response()->json([
                    'status' => false,
                    'message' => 'كود المشرف غير صحيح'
                ], 422);
            }
        }

        // ---------- إنشاء المستخدم ----------
        $user = User::create([
            'role' => $request->role,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

            'phone_number' => $request->phone_number,
            'university_id' => $request->university_id,

            'supervisor_code' => $request->role === 'supervisor'
                ? $supervisorCode
                : $request->supervisor_code,

            'company_name' => $request->company_name,
            'company_website' => $request->company_website,
            'company_address' => $request->company_address,

            'status' => $request->role === 'admin' ? 'active' : 'pending',
        ]);

        $token = $user->createToken('register-token')->plainTextToken;

        // ---------- Response ----------
         return response()->json([
            'status' => true,
            'message' => 'تم إنشاء الحساب بنجاح',
            'data' => $user,  
            'token' => $token,
        ], 201);
    }

    // =========================
    // تسجيل الخروج
    // =========================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful'
        ]);
    }

    // =========================
    // توليد كود السوبرفايزر
    // =========================
    private function generateSupervisorCode(): string
    {
        do {
            $code = 'SUP-' . strtoupper(Str::random(6));
        } while (User::where('supervisor_code', $code)->exists());

        return $code;
    }
}
