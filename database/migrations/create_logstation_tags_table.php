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
        Schema::create('logstation_tags', function (Blueprint $table) {
            $table->uuid('entry_id');
            $table->string('tag', 100);

            $table->primary(['entry_id', 'tag']);
            $table->index('tag');

            $table->foreign('entry_id')
                ->references('id')
                ->on('logstation_entries')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logstation_tags');
    }
};
