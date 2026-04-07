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
    Schema::create('appointment_payments', function (Blueprint $table) {
      $table->uuid()->primary();
      $table->foreignUuid('appointment_uuid')->constrained('appointments', 'uuid')->cascadeOnDelete();
      $table->decimal('amount', 10, 2);
      $table->string('payment_method'); // cash, debit, credit, pix
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('appointment_payments');
  }
};
