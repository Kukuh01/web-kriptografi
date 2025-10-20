<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'key' harus unik (e.g., 'about', 'contact')
            $table->longText('value')->nullable(); // LONGTEXT untuk menampung HTML/Markdown
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descriptions');
    }
};
