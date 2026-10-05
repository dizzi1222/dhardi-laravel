<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portfolio content. Every translatable field lives in a companion
 * translation table keyed by locale, so a language can be added without
 * migrating the base tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('role');
            $table->string('organisation');
            $table->string('organisation_url')->nullable();
            $table->string('employment_type', 32)->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('stack')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'sort_order']);
        });

        Schema::create('experience_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('summary');
            $table->json('highlights')->nullable();
            $table->timestamps();

            $table->unique(['experience_id', 'locale'], 'exp_trans_unique');
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('live_url')->nullable();
            $table->string('case_study_url')->nullable();
            $table->unsignedSmallInteger('year');
            $table->string('status', 24)->default('shipped');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_public_repo')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('stack')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'sort_order']);
            $table->index(['tenant_id', 'is_featured']);
        });

        Schema::create('project_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            // Most project names are proper nouns that stay the same in every
            // language, so the base column remains the source of truth and this
            // one only overrides it when it genuinely differs.
            $table->string('name')->nullable();
            $table->text('summary');
            $table->text('problem')->nullable();
            $table->text('approach')->nullable();
            $table->json('highlights')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'locale'], 'proj_trans_unique');
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('group');
            $table->string('name');
            $table->string('proof')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'group', 'name']);
            $table->index(['tenant_id', 'group']);
        });

        Schema::create('certifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->string('url')->nullable();
            $table->string('status', 24)->default('completed');
            $table->unsignedSmallInteger('issued_year')->nullable();
            // Per-locale notes as {locale: text}. A certification is a handful of
            // rows with a single optional sentence, so a translation table would
            // cost more than it explains.
            $table->json('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('project_translations');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('experience_translations');
        Schema::dropIfExists('experiences');
    }
};
