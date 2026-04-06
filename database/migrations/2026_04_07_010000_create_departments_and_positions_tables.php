<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#6B7280');
            $table->string('category')->default('core'); // core, advanced
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sub_division')->nullable(); // for nested grouping like "SEO Team", "Paid Ads Team"
            $table->integer('level')->default(0); // 0 = staff, 1 = lead, 2 = manager, 3 = head, 4 = c-level
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('email');
            $table->date('birthday')->nullable()->after('gender');
            $table->foreignId('department_id')->nullable()->after('birthday')->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->foreignId('supervisor_id')->nullable()->after('position_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['gender', 'birthday', 'department_id', 'position_id', 'supervisor_id']);
        });

        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
