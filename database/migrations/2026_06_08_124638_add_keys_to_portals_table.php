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
        Schema::table('portals', function (Blueprint $table) {
            $table->text('nestKey')->nullable(); // key from bx шифруются на стороне nest
            $table->text('nestKonstructorKey')->nullable(); // key from bx шифруются на стороне nest
            $table->text('nestReportKey')->nullable(); // key from bx шифруются на стороне nest
            $table->text('nestEventsKey')->nullable(); // key from bx шифруются на стороне nest
            $table->text('nestServiceKey')->nullable(); // key from bx шифруются на стороне nest
            $table->text('nestWebhooksKey')->nullable(); // key from bx шифруются на стороне nest
            $table->text('nestScheduleKey')->nullable(); // key frobx шифруются на стороне nest
            $table->text('vibeKey')->nullable(); // key from bx шифруются на стороне nest

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portals', function (Blueprint $table) {
            //   drop 
            $table->dropColumn('nestKey');
            $table->dropColumn('nestKonstructorKey');
            $table->dropColumn('nestReportKey');
            $table->dropColumn('nestEventsKey');
            $table->dropColumn('nestServiceKey');
            $table->dropColumn('nestWebhooksKey');
            $table->dropColumn('nestScheduleKey');
            $table->dropColumn('vibeKey');
        });
    }
};
