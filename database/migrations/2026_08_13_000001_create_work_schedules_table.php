<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('work_schedule_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_schedule_id')->constrained('work_schedules')->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $table->boolean('is_working_day')->default(true);
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('16:00:00');
            $table->unsignedInteger('expected_minutes')->default(480);
            $table->timestamps();

            $table->unique(['work_schedule_id', 'day_of_week']);
        });

        // Insert default standard company work schedule (Monday-Thursday & Sunday 08:00 - 16:00, Friday/Saturday Off)
        $scheduleId = DB::table('work_schedules')->insertGetId([
            'name' => 'Standard Work Schedule (8:00 AM - 4:00 PM)',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $days = [
            0 => ['is_working' => true],  // Sunday
            1 => ['is_working' => true],  // Monday
            2 => ['is_working' => true],  // Tuesday
            3 => ['is_working' => true],  // Wednesday
            4 => ['is_working' => true],  // Thursday
            5 => ['is_working' => false], // Friday
            6 => ['is_working' => false], // Saturday
        ];

        foreach ($days as $dayOfWeek => $info) {
            DB::table('work_schedule_days')->insert([
                'work_schedule_id' => $scheduleId,
                'day_of_week' => $dayOfWeek,
                'is_working_day' => $info['is_working'],
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'expected_minutes' => 480,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedule_days');
        Schema::dropIfExists('work_schedules');
    }
};
