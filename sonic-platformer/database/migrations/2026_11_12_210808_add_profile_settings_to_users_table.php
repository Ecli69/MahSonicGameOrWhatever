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
    Schema::table('users', function (Blueprint $table) {
        $table->string('avatar')->default('icon1.png'); // Иконка по умолчанию
        $table->string('color')->default('#ffcc00'); // Желтый цвет по умолчанию
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['avatar', 'color']);
    });
}

};
