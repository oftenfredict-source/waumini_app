<?php

namespace Tests\Unit;

use App\Enums\MaritalStatus;
use App\Enums\MemberStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Models\Church;
use App\Models\Member;
use App\Services\Church\MemberService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class EnvelopeReleasedOnDeleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('member_dependants');
        Schema::dropIfExists('members');
        Schema::dropIfExists('users');
        Schema::dropIfExists('churches');

        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('branches_enabled')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('church_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('member_number')->nullable();
            $table->string('envelope_number', 10)->nullable();
            $table->string('spouse_envelope_number', 10)->nullable();
            $table->unsignedBigInteger('spouse_member_id')->nullable();
            $table->string('full_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('status')->nullable();
            $table->string('membership_type')->nullable();
            $table->string('member_type')->nullable();
            $table->string('marital_status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('member_dependants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('linked_member_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_deleting_member_frees_envelope_held_on_spouse_record(): void
    {
        $church = Church::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Church',
            'slug' => 'test-church',
            'branches_enabled' => false,
        ]);

        $husband = Member::query()->create([
            'uuid' => (string) Str::uuid(),
            'church_id' => $church->id,
            'member_number' => 'T-1',
            'envelope_number' => '101',
            'full_name' => 'Husband',
            'gender' => 'male',
            'status' => MemberStatus::Active->value,
            'membership_type' => MembershipType::Permanent->value,
            'member_type' => MemberType::Father->value,
            'marital_status' => MaritalStatus::Married->value,
        ]);

        $wife = Member::query()->create([
            'uuid' => (string) Str::uuid(),
            'church_id' => $church->id,
            'member_number' => 'T-2',
            'envelope_number' => '102',
            'spouse_envelope_number' => '101',
            'spouse_member_id' => $husband->id,
            'full_name' => 'Wife',
            'gender' => 'female',
            'status' => MemberStatus::Active->value,
            'membership_type' => MembershipType::Permanent->value,
            'member_type' => MemberType::Mother->value,
            'marital_status' => MaritalStatus::Married->value,
        ]);

        $husband->update(['spouse_member_id' => $wife->id, 'spouse_envelope_number' => '102']);

        $service = app(MemberService::class);

        $this->assertFalse($service->isEnvelopeAvailable($church, '101'));

        $service->deleteMember($husband->fresh());

        $this->assertNull(Member::withTrashed()->find($husband->id)?->envelope_number);
        $this->assertNull($wife->fresh()->spouse_envelope_number);
        $this->assertNull($wife->fresh()->spouse_member_id);
        $this->assertTrue($service->isEnvelopeAvailable($church, '101'));
    }
}
