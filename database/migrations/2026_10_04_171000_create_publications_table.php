<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title', 120);
            $table->string('slug', 150)->unique();
            $table->text('description');
            $table->string('modality', 20);
            $table->string('condition', 20);
            // Precio en pesos colombianos (COP), sin decimales. Solo para modalidad `sale`.
            $table->unsignedBigInteger('price')->nullable();
            $table->string('wanted_in_exchange', 255)->nullable();
            $table->string('location', 150);
            $table->string('status', 20)->default('available');

            // Moderación: una publicación oculta no aparece en el catálogo.
            $table->timestamp('hidden_at')->nullable();
            $table->string('hidden_reason', 255)->nullable();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('modality');
            $table->index('price');
            $table->index('hidden_at');
            $table->index(['status', 'created_at']);
        });

        Schema::create('publication_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->string('path', 255);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();

            $table->index(['publication_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_images');
        Schema::dropIfExists('publications');
    }
};
