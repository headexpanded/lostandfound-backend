<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->text('backstory')->nullable();
            $table->json('keywords')->nullable();
            $table->string('status')->default('active'); // active, found, expired
            $table->decimal('fee_paid', 8, 2);
            $table->timestamp('lost_date')->nullable();
            $table->timestamps();

            // Indexes for better search performance
            $table->index(['status', 'lost_date']);
            $table->index(['location']);
            $table->fullText(['title', 'description', 'backstory']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_items');
    }
};
