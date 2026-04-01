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
    Schema::create('clients', function (Blueprint $table) {
      $table->uuid()->primary();
      $table->foreignUuid('user_uuid')
        ->constrained('users', 'uuid')
        ->cascadeOnDelete();
      $table->string('name');
      $table->char('initials', 2)->nullable();
      $table->date('date_of_birth');
      $table->string('document')->unique();
      $table->string('email')->nullable()->unique();
      $table->string('phone', 20)->nullable();
      $table->string('color', 7)->nullable();
      $table->timestamps();
      $table->foreignUuid('created_by')->nullable()
        ->constrained('users', 'uuid')->nullOnDelete();
      $table->foreignUuid('updated_by')->nullable()
        ->constrained('users', 'uuid')->nullOnDelete();
      $table->string('notification_token')->nullable()->unique();
      $table->boolean('notifications_enabled')->default(true);
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('clients');
  }
};
