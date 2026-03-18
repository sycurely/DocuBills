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
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null')->after('full_name');
            }
            if (! Schema::hasColumn('users', 'is_suspended')) {
                $table->boolean('is_suspended')->default(false)->after('role_id');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('is_suspended');
            }
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Drop columns if they exist (using raw SQL for safety)
        if (Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_verified_at');
            });
        }

        if (Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
            }
            $columnsToDrop = ['username', 'full_name', 'role_id', 'is_suspended', 'avatar', 'deleted_at'];
            $existingColumns = array_filter($columnsToDrop, fn($col) => Schema::hasColumn('users', $col));
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
    }
};
