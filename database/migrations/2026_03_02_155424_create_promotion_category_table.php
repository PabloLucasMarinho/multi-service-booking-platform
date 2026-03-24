<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('promotion_category', function (Blueprint $table) {
      $table->foreignUuid('promotion_uuid')
        ->constrained('promotions', 'uuid')
        ->cascadeOnDelete();
      $table->foreignUuid('category_uuid')
        ->constrained('categories', 'uuid')
        ->cascadeOnDelete();
      $table->primary(['promotion_uuid', 'category_uuid']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('promotion_category');
  }
};
