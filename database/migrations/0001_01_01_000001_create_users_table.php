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
    Schema::create('users', function (Blueprint $table) {
      $table->uuid()->primary();
      $table->string('name');
      $table->char('initials', 2)->nullable();
      $table->string('email')->unique()->nullable();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password', 200)->nullable();
      $table->string('color', 7)->nullable();
      $table->foreignUuid('role_uuid')
        ->constrained('roles', 'uuid')
        ->cascadeOnDelete();
      $table->string('document')->unique()->nullable();
      $table->date('date_of_birth');
      $table->string('phone', 20);
      $table->string('zip_code');
      $table->string('address');
      $table->string('address_number')->nullable();
      $table->string('address_complement')->nullable();
      $table->string('neighborhood');
      $table->string('city');
      $table->string('state');
      $table->decimal('salary', 10)->nullable();
      $table->date('admission_date')->nullable();
      $table->boolean('can_apply_manual_discount')->default(false);
      $table->rememberToken();
      $table->timestamps();
      $table->foreignUuid('created_by')->nullable()
        ->constrained('users', 'uuid')->nullOnDelete();
      $table->foreignUuid('updated_by')->nullable()
        ->constrained('users', 'uuid')->nullOnDelete();
      $table->softDeletes();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
      $table->string('email')->primary();
      $table->string('token');
      $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->uuid('user_id')->nullable()->index();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->longText('payload');
      $table->integer('last_activity')->index();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
    Schema::dropIfExists('password_reset_tokens');
    Schema::dropIfExists('sessions');
  }
};
