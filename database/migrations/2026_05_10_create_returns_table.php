<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inverse_logistics_returns', function (Blueprint $table) {
            $table->id();
            $table->date('route_date')->note('Date of the return route');
            $table->string('reference')->note('External reference ID for the return');
            $table->string('client_id')->index()->note('ID of the client initiating the return');
            $table->string('truck_number')->index()->note('License Plate of the truck assigned to the return');
            $table->string('driver_id')->index()->note('ID of the driver handling the return');
            $table->string('driver_name')->nullable()->note('Name of the driver handling the return');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index()->note('Current status of the return');
            $table->json('payload')->nullable()->note('Original payload of the return request');
            $table->text('notes')->nullable()->note('Additional notes for the return');
            $table->timestamp('approved_at')->nullable()->note('Timestamp when the return was approved');
            $table->timestamp('rejected_at')->nullable()->note('Timestamp when the return was rejected');
            $table->timestamps();

            $table->unique(['client_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inverse_logistics_returns');
    }
};
