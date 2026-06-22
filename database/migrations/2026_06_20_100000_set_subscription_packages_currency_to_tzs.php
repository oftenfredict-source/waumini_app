<?php

use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SubscriptionPackage::query()
            ->where(function ($query) {
                $query->whereNull('currency')->orWhere('currency', 'USD');
            })
            ->update(['currency' => 'TZS']);

        SystemSetting::setValue('billing', 'currency', 'TZS');
        SystemSetting::setValue('churches', 'default_currency', 'TZS');
    }

    public function down(): void
    {
        SystemSetting::setValue('billing', 'currency', 'USD');
        SystemSetting::setValue('churches', 'default_currency', 'USD');
    }
};
