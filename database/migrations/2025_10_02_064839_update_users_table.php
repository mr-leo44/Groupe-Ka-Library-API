<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clan_id')->nullable()->constrained('clans')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('provider')->nullable(); // social provider
            $table->string('provider_id')->nullable();
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clan_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn(['clan_id','city_id','phone','avatar','provider','provider_id']);
        });
    }
};
