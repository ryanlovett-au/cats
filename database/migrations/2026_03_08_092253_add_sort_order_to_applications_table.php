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
        if (Schema::hasColumn('applications', 'sort_order')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('emoji');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
