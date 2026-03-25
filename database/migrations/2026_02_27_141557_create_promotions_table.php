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
    Schema::create('promotions', function (Blueprint $table) {
      $table->uuid()->primary();
      $table->string('name');
      $table->string('type');
      $table->decimal('value', 5);
      $table->dateTime('starts_at');
      $table->dateTime('ends_at');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('promotions');
  }
};
