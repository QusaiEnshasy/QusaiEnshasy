<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'student_id',
        'opportunity_id',
        'skills',
        'motivation',
        'cv',
        'company_status',
        'supervisor_status',
        'final_status',
        'approved_at'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function opportunity()
    {
        return $this->belongsTo(InternshipOpportunity::class, 'opportunity_id');
    }
}
