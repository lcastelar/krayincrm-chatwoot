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
        if (! Schema::hasTable('chatwoot_webhook_logs')) {
            Schema::create('chatwoot_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->string('event')->nullable();
                $table->string('source', 20)->default('chatwoot'); // 'chatwoot' or 'krayin'
                $table->string('status', 20)->default('success'); // 'success', 'failed', 'ignored'
                $table->unsignedSmallInteger('response_code')->default(200);
                $table->text('summary')->nullable();
                $table->longText('payload')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index('event');
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatwoot_webhook_logs');
    }
};
