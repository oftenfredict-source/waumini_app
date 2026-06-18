<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('member_type', 20)->nullable()->after('membership_type');
            $table->string('envelope_number', 10)->nullable()->after('member_number');
            $table->string('education_level', 30)->nullable()->after('date_of_birth');
            $table->string('profession')->nullable()->after('education_level');
            $table->string('nida_number', 50)->nullable()->after('profession');
            $table->string('profile_picture')->nullable()->after('nida_number');
            $table->string('district')->nullable()->after('region');
            $table->string('ward')->nullable()->after('district');
            $table->string('street')->nullable()->after('ward');
            $table->string('po_box', 100)->nullable()->after('street');
            $table->string('tribe')->nullable()->after('po_box');
            $table->string('other_tribe')->nullable()->after('tribe');
            $table->string('residence_region')->nullable()->after('other_tribe');
            $table->string('residence_district')->nullable()->after('residence_region');
            $table->string('residence_ward')->nullable()->after('residence_district');
            $table->string('residence_street')->nullable()->after('residence_ward');
            $table->string('residence_road')->nullable()->after('residence_street');
            $table->string('residence_house_number', 50)->nullable()->after('residence_road');
            $table->string('spouse_full_name')->nullable()->after('marital_status');
            $table->string('spouse_gender', 20)->nullable()->after('spouse_full_name');
            $table->date('spouse_date_of_birth')->nullable()->after('spouse_gender');
            $table->string('spouse_education_level', 30)->nullable()->after('spouse_date_of_birth');
            $table->string('spouse_profession')->nullable()->after('spouse_education_level');
            $table->string('spouse_nida_number', 50)->nullable()->after('spouse_profession');
            $table->string('spouse_email')->nullable()->after('spouse_nida_number');
            $table->string('spouse_phone_number', 30)->nullable()->after('spouse_email');
            $table->string('spouse_tribe')->nullable()->after('spouse_phone_number');
            $table->string('spouse_other_tribe')->nullable()->after('spouse_tribe');
            $table->string('spouse_church_member', 10)->nullable()->after('spouse_other_tribe');
            $table->foreignId('spouse_member_id')->nullable()->after('spouse_church_member')->constrained('members')->nullOnDelete();
            $table->string('spouse_envelope_number', 10)->nullable()->after('spouse_member_id');

            $table->unique(['church_id', 'envelope_number']);
        });

        if (Schema::hasColumn('members', 'occupation')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('occupation');
            });
        }

        Schema::create('member_dependants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('gender', 20);
            $table->date('date_of_birth')->nullable();
            $table->string('relationship', 30);
            $table->string('relationship_note')->nullable();
            $table->foreignId('linked_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_dependants');

        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['church_id', 'envelope_number']);
            $table->dropConstrainedForeignId('spouse_member_id');
            $table->dropColumn([
                'member_type', 'envelope_number', 'education_level', 'profession', 'nida_number', 'profile_picture',
                'district', 'ward', 'street', 'po_box', 'tribe', 'other_tribe',
                'residence_region', 'residence_district', 'residence_ward', 'residence_street', 'residence_road', 'residence_house_number',
                'spouse_full_name', 'spouse_gender', 'spouse_date_of_birth', 'spouse_education_level', 'spouse_profession',
                'spouse_nida_number', 'spouse_email', 'spouse_phone_number', 'spouse_tribe', 'spouse_other_tribe',
                'spouse_church_member', 'spouse_envelope_number',
            ]);
            $table->string('occupation')->nullable();
        });
    }
};
