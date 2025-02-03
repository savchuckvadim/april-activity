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
        Schema::table('infoblocks', function (Blueprint $table) {
            if (!Schema::hasColumn('infoblocks', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('infoblocks');
            }
            if (!Schema::hasColumn('infoblocks', 'relation_id')) {
                $table->foreignId('relation_id')->nullable()->constrained('infoblocks');
            }
            if (!Schema::hasColumn('infoblocks', 'related_id')) {
                $table->foreignId('related_id')->nullable()->constrained('infoblocks');
            }
            if (!Schema::hasColumn('infoblocks', 'excluded_id')) {
                $table->foreignId('excluded_id')->nullable()->constrained('infoblocks');
            }
            if (!Schema::hasColumn('infoblocks', 'group_id')) {
                $table->foreignId('group_id')->nullable()->constrained('info_groups');
            }
            if (!Schema::hasColumn('infoblocks', 'isProduct')) {
                $table->boolean('isProduct')->nullable();
            }
            if (!Schema::hasColumn('infoblocks', 'isPackage')) {
                $table->boolean('isPackage')->nullable();
            }
            if (!Schema::hasColumn('infoblocks', 'tag')) {
                $table->string('tag')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infoblocks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropForeign(['relation_id']);
            $table->dropColumn('relation_id');
            $table->dropForeign(['related_id']);
            $table->dropColumn(['related_id']);
          
            $table->dropForeign(['group_id']);
            $table->dropColumn(['group_id']);
            $table->dropForeign(['excluded_id']);
            $table->dropColumn(['excluded_id']);
            $table->dropColumn(['isProduct']);
            $table->dropColumn(['tag']);


        });
    }
};
