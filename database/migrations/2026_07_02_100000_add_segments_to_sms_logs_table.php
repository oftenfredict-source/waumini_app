<?php

use App\Support\SmsSegmentCounter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('segments')->default(1)->after('message');
        });

        DB::table('sms_logs')->orderBy('id')->chunkById(500, function ($logs): void {
            foreach ($logs as $log) {
                DB::table('sms_logs')->where('id', $log->id)->update([
                    'segments' => SmsSegmentCounter::count($log->message ?? ''),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropColumn('segments');
        });
    }
};
