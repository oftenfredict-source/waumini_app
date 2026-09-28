<?php

namespace Tests\Unit;

use App\Models\Member;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UniqueEnvelopeIgnoresDeletedMembersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('members');
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('church_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('envelope_number', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_unique_envelope_rule_blocks_active_members(): void
    {
        Member::query()->create([
            'church_id' => 1,
            'envelope_number' => '101',
        ]);

        $this->assertFalse(
            Validator::make(
                ['envelope_number' => '101'],
                ['envelope_number' => Member::uniqueEnvelopeRule(1, null, false)]
            )->passes()
        );
    }

    public function test_unique_envelope_rule_allows_reusing_a_deleted_members_number(): void
    {
        $member = Member::query()->create([
            'church_id' => 1,
            'envelope_number' => '101',
        ]);

        $member->delete();

        $this->assertTrue(
            Validator::make(
                ['envelope_number' => '101'],
                ['envelope_number' => Member::uniqueEnvelopeRule(1, null, false)]
            )->passes()
        );
    }
}
