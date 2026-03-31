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
    Schema::create('appointments', function (Blueprint $table) {
      $table->uuid()->primary();
      $table->foreignUuid('user_uuid')
        ->constrained('users', 'uuid')
        ->cascadeOnDelete();
      $table->foreignUuid('client_uuid')
        ->constrained('clients', 'uuid')
        ->cascadeOnDelete();
      $table->dateTime('scheduled_at');
      $table->text('notes')->nullable();
      $table->string('status');
      $table->timestamps();
      $table->foreignUuid('created_by')->nullable()
        ->constrained('users', 'uuid')->nullOnDelete();
      $table->foreignUuid('updated_by')->nullable()
        ->constrained('users', 'uuid')->nullOnDelete();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('appointments');
  }
};
