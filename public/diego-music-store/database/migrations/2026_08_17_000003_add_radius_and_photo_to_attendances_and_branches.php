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
        // Add Lat, Long, Radius to branches table
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
            }
            if (!Schema::hasColumn('branches', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('branches', 'attendance_radius_meters')) {
                $table->integer('attendance_radius_meters')->default(100)->after('longitude');
            }
        });

        // Add GPS, Photos, Distance to employee_attendances table
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendances', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('late_minutes');
            }
            if (!Schema::hasColumn('employee_attendances', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('employee_attendances', 'distance_meters')) {
                $table->integer('distance_meters')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('employee_attendances', 'is_out_of_radius')) {
                $table->boolean('is_out_of_radius')->default(false)->after('distance_meters');
            }
            if (!Schema::hasColumn('employee_attendances', 'clock_in_photo_path')) {
                $table->string('clock_in_photo_path')->nullable()->after('is_out_of_radius');
            }
            if (!Schema::hasColumn('employee_attendances', 'clock_out_photo_path')) {
                $table->string('clock_out_photo_path')->nullable()->after('clock_in_photo_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'attendance_radius_meters']);
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'distance_meters',
                'is_out_of_radius',
                'clock_in_photo_path',
                'clock_out_photo_path',
            ]);
        });
    }
};
