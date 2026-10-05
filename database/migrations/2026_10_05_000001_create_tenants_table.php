<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customers are isolated by deployment: one instance per customer.
 * The table exists so that content, observability and the assistant
 * conversations always carry an explicit owner.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('default_locale', 5)->default('de');
            $table->string('brand_name')->nullable();
            $table->string('brand_accent', 32)->default('#C8102E');
            $table->string('plan', 32)->default('standard');
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'plan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
