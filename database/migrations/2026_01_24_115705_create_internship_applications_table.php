<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

          $table->foreignId('student_id')
          ->constrained('users')
          ->cascadeOnDelete();

   $table->foreignId('opportunity_id')
      ->constrained('internship_opportunities')
      ->cascadeOnDelete();


        
            $table->text('skills')->nullable();        
            $table->text('motivation')->nullable();

            $table->enum('status', [
                'company_accepted',
                'assigned',
                'supervisor_rejected',
                'supervisor_accepted',
                'company_rejected',
                'pending'
            ])->default('pending');

            $table->string('cv'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
