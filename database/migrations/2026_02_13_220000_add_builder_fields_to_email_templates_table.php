<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        Schema::table('email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('email_templates', 'html_content')) {
                $table->longText('html_content')->nullable()->after('body');
            }

            if (!Schema::hasColumn('email_templates', 'design_json')) {
                $table->longText('design_json')->nullable()->after('html_content');
            }
        });

        if (Schema::hasColumn('email_templates', 'body') && Schema::hasColumn('email_templates', 'html_content')) {
            DB::table('email_templates')
                ->where(function ($query) {
                    $query->whereNull('html_content')
                        ->orWhere('html_content', '');
                })
                ->whereNotNull('body')
                ->where('body', '!=', '')
                ->update(['html_content' => DB::raw('body')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        Schema::table('email_templates', function (Blueprint $table) {
            if (Schema::hasColumn('email_templates', 'design_json')) {
                $table->dropColumn('design_json');
            }

            if (Schema::hasColumn('email_templates', 'html_content')) {
                $table->dropColumn('html_content');
            }
        });
    }
};
