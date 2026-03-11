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
    Schema::create('category_service', function (Blueprint $table) {
      $table->foreignUuid('category_uuid')
        ->constrained('categories', 'uuid')
        ->cascadeOnDelete();
      $table->foreignUuid('service_uuid')
        ->constrained('services', 'uuid')
        ->cascadeOnDelete();
      $table->primary(['category_uuid', 'service_uuid']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('category_service');
  }
};
