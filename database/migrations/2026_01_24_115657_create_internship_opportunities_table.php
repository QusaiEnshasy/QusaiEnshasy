<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('internship_opportunities', function (Blueprint $table) {
    $table->id();

    // الشركة التي نشرت الفرصة
   $table->foreignId('company_user_id')
          ->constrained('users')
          ->cascadeOnDelete();
    // معلومات أساسية
    $table->string('title');                // اسم الوظيفة / التدريب
    $table->text('description');            // الوصف الكامل
    $table->enum('type', ['training', 'job']); // تدريب أو وظيفة

    // تفاصيل إضافية
    $table->string('field')->nullable();    // المجال (IT, Marketing, Engineering...)
    $table->string('city')->nullable();     // مكان التدريب
    $table->enum('work_type', ['onsite', 'remote', 'hybrid'])->nullable();

    // شروط ومتطلبات
    $table->text('requirements')->nullable(); // متطلبات القبول
    $table->string('education_level')->nullable(); // دبلوم، بكالوريوس...

    // مدة ومواعيد
    $table->integer('duration')->nullable(); // مدة التدريب (بالأسابيع)
    $table->date('deadline')->nullable();    // آخر موعد للتقديم

    // حالة الإعلان
    $table->enum('status', ['open', 'closed'])->default('open');

    $table->timestamps();
});

        // Schema::create('jobs', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('queue')->index();
        //     $table->longText('payload');
        //     $table->unsignedTinyInteger('attempts');
        //     $table->unsignedInteger('reserved_at')->nullable();
        //     $table->unsignedInteger('available_at');
        //     $table->unsignedInteger('created_at');
        // });

        // Schema::create('job_batches', function (Blueprint $table) {
        //     $table->string('id')->primary();
        //     $table->string('name');
        //     $table->integer('total_jobs');
        //     $table->integer('pending_jobs');
        //     $table->integer('failed_jobs');
        //     $table->longText('failed_job_ids');
        //     $table->mediumText('options')->nullable();
        //     $table->integer('cancelled_at')->nullable();
        //     $table->integer('created_at');
        //     $table->integer('finished_at')->nullable();
        // });

        // Schema::create('failed_jobs', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('uuid')->unique();
        //     $table->text('connection');
        //     $table->text('queue');
        //     $table->longText('payload');
        //     $table->longText('exception');
        //     $table->timestamp('failed_at')->useCurrent();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_opportunities');
      }
};
