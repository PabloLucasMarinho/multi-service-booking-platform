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
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('tip', 10, 2)->nullable()->after('status');
            $table->decimal('closing_discount', 10, 2)->nullable()->after('tip');
            $table->foreignUuid('discount_authorized_by')->nullable()->after('closing_discount')->constrained('users', 'uuid');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['discount_authorized_by']);
            $table->dropColumn(['tip', 'closing_discount', 'discount_authorized_by']);
        });
    }
};
