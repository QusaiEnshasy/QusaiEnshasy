<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipOpportunity extends Model
{
    protected $fillable = [
     'company_user_id',
        'title',
        'description',
        'type',
        'field',
        'city',
        'work_type',
        'requirements',
        'education_level',
        'duration',
        'deadline',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_user_id');
    }
}
