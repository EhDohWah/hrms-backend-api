<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module'); // employee, holidays, leaves, etc.
            $table->enum('action', ['read', 'write', 'create', 'delete', 'import', 'export']);
            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Composite unique key for module and action
            $table->unique(['module', 'action']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
