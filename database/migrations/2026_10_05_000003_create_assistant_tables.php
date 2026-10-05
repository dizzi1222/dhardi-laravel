<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Everything an AI feature needs in order to be operated rather than merely
 * demonstrated: a conversation trail, one row per model attempt, and a golden
 * dataset whose results are re-measured on demand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->uuid('public_id')->unique();
            $table->string('locale', 5)->default('de');
            $table->string('intent', 48)->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16);
            $table->text('content');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        Schema::create('llm_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider');
            $table->string('model');
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->string('outcome', 24);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->boolean('was_cached')->default(false);
            $table->string('error_class')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['provider', 'outcome']);
            $table->index(['outcome', 'created_at'], 'llm_runs_outcome_created_idx');
        });

        Schema::create('llm_eval_cases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('prompt');
            $table->json('expected_contains')->nullable();
            $table->string('locale', 5)->default('de');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
        });

        Schema::create('llm_eval_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('llm_eval_case_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('model');
            $table->boolean('passed');
            $table->unsignedInteger('latency_ms')->default(0);
            $table->text('output')->nullable();
            $table->timestamps();

            $table->index(['llm_eval_case_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_eval_results');
        Schema::dropIfExists('llm_eval_cases');
        Schema::dropIfExists('llm_runs');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
