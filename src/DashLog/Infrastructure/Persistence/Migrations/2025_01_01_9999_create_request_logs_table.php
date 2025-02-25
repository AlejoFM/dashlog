<?php

namespace DashLog\Infrastructure\Persistence\Migrations;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('method');
            $table->text('url');
            $table->string('ip')->nullable();
            $table->string('user_id')->nullable();
            $table->float('duration');
            $table->integer('status_code');
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->json('headers')->nullable();
            $table->json('cookies')->nullable();
            $table->json('session')->nullable();
            $table->json('stack_trace')->nullable();
            $table->json('user_agent')->nullable();
            $table->timestamps();
            $table->index('created_at');
            $table->index('status_code');
            $table->index('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
}; 