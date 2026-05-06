<?php

use App\Enums\LawyerRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_generation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->constrained('users')->cascadeOnDelete();
            $table->text('freelancer_comment');
            $table->text('lawyer_comment')->nullable();
            $table->string('corrected_file_path')->nullable();
            $table->enum('status', array_column(LawyerRequestStatus::cases(), 'value'))
                ->default(LawyerRequestStatus::Pending->value);
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyer_requests');
    }
};
