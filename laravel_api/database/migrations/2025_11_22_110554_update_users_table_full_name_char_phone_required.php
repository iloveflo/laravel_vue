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
        // Cập nhật các record có phone = NULL thành giá trị mặc định
        \DB::table('users')->whereNull('phone')->update(['phone' => '']);
        
        Schema::table('users', function (Blueprint $table) {
            // Đổi full_name từ string (VARCHAR) sang char
            $table->char('full_name', 255)->change();
            
            // Đổi phone từ nullable sang required (NOT NULL)
            $table->string('phone', 20)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert full_name về string
            $table->string('full_name')->change();
            
            // Revert phone về nullable
            $table->string('phone', 20)->nullable()->change();
        });
    }
};
