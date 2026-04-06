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
    Schema::create('company', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('fantasy_name')->nullable();
      $table->string('document')->nullable(); // CNPJ ou CPF
      $table->string('email')->nullable();
      $table->string('phone')->nullable();
      $table->string('zip_code')->nullable();
      $table->string('address')->nullable();
      $table->string('address_number')->nullable();
      $table->string('address_complement')->nullable();
      $table->string('neighborhood')->nullable();
      $table->string('city')->nullable();
      $table->string('state')->nullable();
      $table->unsignedSmallInteger('rebooking_reminder_days')->nullable();
      $table->unsignedTinyInteger('max_discount_percentage')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('companies');
  }
};
