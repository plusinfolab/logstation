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
        Schema::create('logstation_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('batch_id');
            $table->string('type', 20); // emergency, alert, critical, error, warning, notice, info, debug
            $table->string('channel', 50)->nullable();
            $table->integer('level');
            $table->string('level_name', 20);
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('extra')->nullable();

            // Exception details
            $table->string('exception_class')->nullable();
            $table->text('exception_message')->nullable();
            $table->longText('exception_trace')->nullable();
            $table->string('exception_file')->nullable();
            $table->integer('exception_line')->nullable();

            // Request details
            $table->string('request_method', 10)->nullable();
            $table->text('request_url')->nullable();
            $table->string('request_ip', 45)->nullable();
            $table->text('request_user_agent')->nullable();

            // User details
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_email')->nullable();

            // Session & Request tracking
            $table->string('session_id')->nullable();
            $table->string('request_id')->nullable();

            $table->timestamp('created_at')->nullable();

            // Indexes for performance
            $table->index('batch_id');
            $table->index('type');
            $table->index('channel');
            $table->index('level');
            $table->index('user_id');
            $table->index('request_id');
            $table->index('created_at');
            $table->index(['type', 'created_at']);
            $table->index(['channel', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logstation_entries');
    }
};
