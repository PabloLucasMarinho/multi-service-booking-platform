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
    Schema::create('user_details', function (Blueprint $table) {
      $table->uuid()->primary();
      $table->foreignUuid('user_uuid')
        ->constrained('users', 'uuid')
        ->cascadeOnDelete();
      $table->string('document')->unique()->nullable();
      $table->date('date_of_birth');
      $table->string('phone', 20);
      $table->string('address', 100);
      $table->string('address_complement', 50)->nullable();
      $table->string('zip_code', 9);
      $table->string('neighborhood', 50);
      $table->string('city', 50);
      $table->decimal('salary', 10)->nullable();
      $table->date('admission_date');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_details');
  }
};
