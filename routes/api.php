<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\InternshipAssignmentController
;use App\Http\Controllers\InternshipApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Http\Request;

Route::prefix('v1')->group(function () {


    Route::post('/auth/login', [AuthController::class, 'login']);
 Route::post('/auth/register', [AuthController::class, 'register']);


    Route::middleware('auth:sanctum')->group(function () {
        // Route::get('/auth/me', [AuthController::class, 'me']);
          Route::get('/auth/me', function (Request $request) {
        return $request->user();
    });

        Route::post('/auth/logout', [AuthController::class, 'logout']);
           Route::get('/auth/users', [AuthController::class, 'getAllUsers']);
           
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {

            Route::post('/admin/supervisor/{id}', [AdminController::class, 'updateSupervisorStatus']);
            Route::post('/admin/company/{id}', [AdminController::class, 'updateCompanyStatus']);
        });



         Route::get('/supervisor/students', [ApplicationController::class, 'supervisorStudents']);
 
        Route::post('/supervisor/approve-student/{id}',  [ApplicationController::class, 'supervisorActiveStudentAcouunt']  );

        Route::post(  '/supervisor/reject-student/{id}', [ApplicationController::class, 'rejectActiveStudentAcouunt']);

        Route::post('/company/createJob', [JobController::class, 'createJob']);
    Route::post('/company/jobs/{id}', [JobController::class, 'updateJob']);
Route::delete('/company/jobs/{id}', [JobController::class, 'deleteJob']);
Route::post('/application', [ApplicationController::class, 'applyApplication']);


        Route::get('/my-applications', [ApplicationController::class, 'myApplications']);

        Route::get('/company/applications', [ApplicationController::class, 'companyApplications']);
        Route::post('/company/approve/{id}', [ApplicationController::class, 'companyApplicationApprove']);
        Route::post('/company/reject/{id}', [ApplicationController::class, 'companyApplicationReject']);

        Route::get('/supervisor/applications', [ApplicationController::class, 'supervisorApplications']);
        Route::post('/supervisor/approve/{id}', [ApplicationController::class, 'supervisorApplicationApprove']);
        Route::post('/supervisor/reject/{id}', [ApplicationController::class, 'supervisorReject']);
    });






});


 
 