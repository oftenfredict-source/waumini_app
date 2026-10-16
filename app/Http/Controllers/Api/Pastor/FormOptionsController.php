<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Enums\CelebrationStatus;
use App\Enums\CelebrationType;
use App\Enums\ChildEducationLevel;
use App\Enums\ChurchServiceStatus;
use App\Enums\ChurchServiceType;
use App\Enums\DependantRelationship;
use App\Enums\EducationLevel;
use App\Enums\MaritalStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Enums\ServiceCoordinatorType;
use App\Enums\ServicePreacherType;
use App\Enums\TemporaryDurationUnit;
use App\Enums\DepartmentStatus;
use App\Enums\LeadershipPosition;
use App\Enums\SpecialEventCategory;
use App\Enums\SpecialEventStatus;
use App\Enums\WeddingType;
use App\Models\Department;
use App\Models\Leader;
use App\Models\Member;
use App\Services\Church\BranchAccessService;
use App\Services\Church\ChurchServiceService;
use App\Services\Church\ChurchSettingsService;
use App\Support\ApiResponse;
use BackedEnum;
use Illuminate\Http\JsonResponse;

class FormOptionsController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly ChurchServiceService $churchServiceService,
        private readonly ChurchSettingsService $churchSettingsService,
    ) {}

    public function member(): JsonResponse
    {
        $this->authorize('create', Member::class);

        $user = $this->pastor();
        $members = Member::forChurch($user->church_id)
            ->activeMembers()
            ->orderBy('full_name');
        $this->branchAccessService->applyBranchScope($members, $user);

        $branches = $this->branchAccessService->selectableBranches($user);
        if ($effective = $this->branchAccessService->effectiveBranchId($user)) {
            $branches = $branches->where('id', $effective)->values();
        }

        return ApiResponse::success([
            'fields' => [
                'membership_type',
                'temporary_duration_value',
                'temporary_duration_unit',
                'member_type',
                'branch_id',
                'envelope_number',
                'full_name',
                'gender',
                'date_of_birth',
                'education_level',
                'profession',
                'nida_number',
                'is_baptized',
                'baptism_date',
                'baptism_place',
                'baptized_by',
                'is_kipaimara',
                'kipaimara_date',
                'kipaimara_place',
                'kipaimara_by',
                'profile_picture',
                'phone_number',
                'email',
                'region',
                'district',
                'ward',
                'street',
                'po_box',
                'tribe',
                'other_tribe',
                'residence_region',
                'residence_district',
                'residence_ward',
                'residence_street',
                'residence_road',
                'residence_house_number',
                'marital_status',
                'wedding_type',
                'wedding_date',
                'spouse_church_member',
                'spouse_input_method',
                'spouse_member_id',
                'spouse_full_name',
                'spouse_gender',
                'spouse_date_of_birth',
                'spouse_education_level',
                'spouse_profession',
                'spouse_nida_number',
                'spouse_email',
                'spouse_phone_number',
                'spouse_tribe',
                'spouse_other_tribe',
                'spouse_envelope_number',
                'family_parent_type',
                'family_member_id',
                'guardian_full_name',
                'guardian_phone',
                'guardian_relationship',
                'dependants',
            ],
            'membership_types' => $this->options(MembershipType::cases()),
            'member_types' => $this->options(MemberType::cases()),
            'education_levels' => $this->options(EducationLevel::cases()),
            'child_education_levels' => $this->options(ChildEducationLevel::cases()),
            'marital_statuses' => $this->options(MaritalStatus::cases()),
            'wedding_types' => $this->options(WeddingType::cases()),
            'duration_units' => $this->options(TemporaryDurationUnit::cases()),
            'dependant_relationships' => $this->options(DependantRelationship::cases()),
            'genders' => [
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
            ],
            'tribes' => config('tanzania.tribes', []),
            'locations' => $this->tanzaniaLocations(),
            'branches' => $branches->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
            ])->values()->all(),
            'default_branch_id' => $this->branchAccessService->resolveBranchIdForCreate($user, null),
            'members' => $members->get(['id', 'full_name', 'member_number', 'envelope_number', 'gender', 'phone_number'])->map(fn (Member $member) => [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'member_number' => $member->member_number,
                'envelope_number' => $member->envelope_number,
                'gender' => $member->gender,
                'phone_number' => $member->phone_number,
            ])->values()->all(),
            'guardian_relationships' => [
                'Father', 'Mother', 'Guardian', 'Uncle', 'Aunt', 'Grandparent', 'Brother', 'Sister', 'Other',
            ],
            'settings' => [
                'kipaimara_registration_enabled' => (bool) $this->churchSettingsService->get($this->church(), 'kipaimara_registration_enabled', false),
                'kipaimara_min_age' => $this->churchSettingsService->kipaimaraMinAge($this->church()),
                'envelope_required_from_age' => $this->churchSettingsService->envelopeRequiredFromAge($this->church()),
                'children_education_details_enabled' => (bool) $this->churchSettingsService->get($this->church(), 'children_education_details_enabled', false),
            ],
        ]);
    }

    public function service(): JsonResponse
    {
        $this->authorize('create', \App\Models\ChurchService::class);

        $user = $this->pastor();
        $church = $this->church();
        $branches = $this->branchAccessService->selectableBranches($user);
        if ($effective = $this->branchAccessService->effectiveBranchId($user)) {
            $branches = $branches->where('id', $effective)->values();
        }

        return ApiResponse::success([
            'fields' => [
                'service_type',
                'title',
                'service_date',
                'start_time',
                'end_time',
                'theme',
                'preacher_type',
                'preacher_member_id',
                'preacher_guest_name',
                'preacher_guest_phone',
                'coordinator_type',
                'coordinator_member_id',
                'coordinator_guest_name',
                'coordinator_guest_phone',
                'venue',
                'status',
                'notes',
                'branch_id',
            ],
            'service_types' => $this->options(ChurchServiceType::cases()),
            'statuses' => $this->options(ChurchServiceStatus::cases()),
            'preacher_types' => $this->options(ServicePreacherType::cases()),
            'coordinator_types' => $this->options(ServiceCoordinatorType::cases()),
            'pastors' => $this->churchServiceService->pastorsForChurch($church)->values()->all(),
            'leaders' => $this->churchServiceService->leadersForChurch($church)->values()->all(),
            'branches' => $branches->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
            ])->values()->all(),
            'default_branch_id' => $this->branchAccessService->resolveBranchIdForCreate($user, null),
        ]);
    }

    public function event(): JsonResponse
    {
        $this->authorize('create', \App\Models\SpecialEvent::class);

        $user = $this->pastor();
        $branches = $this->branchAccessService->selectableBranches($user);
        if ($effective = $this->branchAccessService->effectiveBranchId($user)) {
            $branches = $branches->where('id', $effective)->values();
        }

        return ApiResponse::success([
            'fields' => [
                'title',
                'category',
                'category_other',
                'event_date',
                'start_time',
                'end_time',
                'speaker',
                'venue',
                'budget_amount',
                'expected_attendance',
                'status',
                'description',
                'notes',
                'branch_id',
            ],
            'categories' => $this->options(SpecialEventCategory::cases()),
            'statuses' => $this->options(SpecialEventStatus::cases()),
            'branches' => $branches->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
            ])->values()->all(),
            'default_branch_id' => $this->branchAccessService->resolveBranchIdForCreate($user, null),
            'branches_enabled' => $this->branchAccessService->branchesFeatureEnabled($user),
        ]);
    }

    public function celebration(): JsonResponse
    {
        $this->authorize('create', \App\Models\Celebration::class);

        return ApiResponse::success([
            'fields' => [
                'celebration_type',
                'member_id',
                'title',
                'celebration_date',
                'original_date',
                'wedding_type',
                'venue',
                'start_time',
                'end_time',
                'special_guest',
                'notes',
            ],
            'celebration_types' => $this->options(CelebrationType::cases()),
            'wedding_types' => $this->options(WeddingType::cases()),
            'statuses' => $this->options(CelebrationStatus::cases()),
        ]);
    }

    public function leader(): JsonResponse
    {
        $this->authorize('create', Leader::class);

        return ApiResponse::success([
            'fields' => [
                'member_id',
                'position',
                'position_title',
                'appointment_date',
                'end_date',
                'appointed_by',
                'description',
                'notes',
            ],
            'positions' => $this->options(LeadershipPosition::cases()),
        ]);
    }

    public function department(): JsonResponse
    {
        $this->authorize('create', Department::class);

        $user = $this->pastor();
        $branches = $this->branchAccessService->selectableBranches($user);
        if ($effective = $this->branchAccessService->effectiveBranchId($user)) {
            $branches = $branches->where('id', $effective)->values();
        }

        return ApiResponse::success([
            'fields' => [
                'name',
                'description',
                'head_id',
                'status',
                'branch_id',
            ],
            'statuses' => $this->options(DepartmentStatus::cases()),
            'branches' => $branches->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
            ])->values()->all(),
            'default_branch_id' => $this->branchAccessService->resolveBranchIdForCreate($user, null),
            'branches_enabled' => $this->branchAccessService->branchesFeatureEnabled($user),
        ]);
    }

    /**
     * @param  list<BackedEnum>  $cases
     * @return list<array{value: string, label: string}>
     */
    private function options(array $cases): array
    {
        return array_map(fn (BackedEnum $case) => [
            'value' => (string) $case->value,
            'label' => method_exists($case, 'label') ? $case->label() : $case->name,
        ], $cases);
    }

    /**
     * @return array{regions: list<array{name: string, districts: list<string>}>}
     */
    private function tanzaniaLocations(): array
    {
        $path = public_path('data/tanzania-locations.json');
        if (! is_file($path)) {
            return ['regions' => []];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            return ['regions' => []];
        }
        $regions = is_array($decoded['regions'] ?? null) ? $decoded['regions'] : [];

        return [
            'regions' => collect($regions)->map(function ($region) {
                $districts = collect($region['districts'] ?? [])->map(function ($district) {
                    if (is_string($district)) {
                        return $district;
                    }

                    return is_array($district) ? (string) ($district['name'] ?? '') : '';
                })->filter()->values()->all();

                return [
                    'name' => (string) ($region['name'] ?? ''),
                    'districts' => $districts,
                ];
            })->filter(fn ($region) => $region['name'] !== '')->values()->all(),
        ];
    }
}
