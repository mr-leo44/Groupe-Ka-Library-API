<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dioceses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('doyennes', function (Blueprint $table) { // doyennés
            $table->id();
            $table->foreignId('diocese_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['diocese_id','name']);
        });

        Schema::create('clans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doyenne_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['doyenne_id','name']);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->unique(['name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('clans');
        Schema::dropIfExists('deaneries');
        Schema::dropIfExists('dioceses');
    }
};
