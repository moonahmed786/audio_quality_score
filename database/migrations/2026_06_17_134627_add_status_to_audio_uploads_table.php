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
        Schema::table('audio_uploads', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('is_duration_outlier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audio_uploads', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
