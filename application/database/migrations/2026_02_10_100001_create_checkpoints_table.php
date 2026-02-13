<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->string('status')->nullable();
            $table->string('end_transition')->nullable();
            $table->text('end_comment')->nullable();
            $table->jsonb('transition_names')->nullable();
            $table->jsonb('visible_to_permissions')->nullable();
            $table->jsonb('visible_to_user_ids')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->index('task_id');
            $table->index('responsible_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkpoints');
    }
};
