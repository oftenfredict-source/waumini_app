<?php

namespace App\Services\Church;

use App\Enums\DepartmentStatus;
use App\Enums\DependantRelationship;
use App\Enums\LeadershipPosition;
use App\Models\Church;
use App\Models\Department;
use App\Models\Member;
use App\Models\MemberDependant;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class DepartmentAssignmentService
{
    public function __construct(
        private readonly ChurchSettingsService $churchSettingsService,
        private readonly DepartmentService $departmentService,
    ) {}

    /**
     * Attach the member to departments they match, and remove rule-based
     * memberships they no longer qualify for (e.g. after leadership ends).
     *
     * @return int Number of departments the member was newly attached to
     */
    public function assignIfApplicable(Church $church, Member $member): int
    {
        if (! $this->isEnabled($church)) {
            return 0;
        }

        $rules = $this->rules($church);

        if ($rules === []) {
            return 0;
        }

        $member->loadMissing([
            'leaders' => fn ($query) => $query->where('is_active', true),
            'departments',
        ]);

        return $this->syncMemberDepartments($church, $member, $rules)['attached'];
    }

    /**
     * Attach a child/dependant to age-based departments they match.
     *
     * @return int Number of departments the dependant was newly attached to
     */
    public function assignDependantIfApplicable(Church $church, MemberDependant $dependant): int
    {
        if (! $this->isEnabled($church)) {
            return 0;
        }

        if ($dependant->linked_member_id) {
            return 0;
        }

        $rules = $this->ageOnlyRules($this->rules($church));

        if ($rules === []) {
            return 0;
        }

        return $this->attachMatchingDependantDepartments($church, $dependant, $rules);
    }

    /**
     * Re-evaluate one dependant against age-based department rules.
     */
    public function refreshDependantAssignment(Church $church, MemberDependant $dependant): void
    {
        if (! $this->isEnabled($church)) {
            return;
        }

        $rules = $this->ageOnlyRules($this->rules($church));

        if ($rules === []) {
            return;
        }

        $dependant->loadMissing('departments');
        $this->syncDependantDepartments($church, $dependant, $rules);
    }

    /**
     * Apply current rules to existing members and children (dependants).
     *
     * @return array{scanned: int, matched: int, attached: int, removed: int, enabled: bool}
     */
    public function syncExistingMembers(Church $church): array
    {
        $church->refresh();

        if (! $this->isEnabled($church)) {
            return [
                'scanned' => 0,
                'matched' => 0,
                'attached' => 0,
                'removed' => 0,
                'enabled' => false,
            ];
        }

        $rules = $this->rules($church);

        if ($rules === []) {
            return [
                'scanned' => 0,
                'matched' => 0,
                'attached' => 0,
                'removed' => 0,
                'enabled' => true,
            ];
        }

        $scanned = 0;
        $matched = 0;
        $attached = 0;
        $removed = 0;

        Member::forChurch($church->id)
            ->activeMembers()
            ->with(['leaders' => fn ($query) => $query->where('is_active', true), 'departments'])
            ->orderBy('id')
            ->chunkById(100, function (Collection $members) use ($church, $rules, &$scanned, &$matched, &$attached, &$removed) {
                foreach ($members as $member) {
                    $scanned++;
                    $result = $this->syncMemberDepartments($church, $member, $rules);
                    $attached += $result['attached'];
                    $removed += $result['removed'];

                    if ($result['attached'] > 0 || $result['matched']) {
                        $matched++;
                    }
                }
            });

        // Inactive/archived members never match active rules — drop rule-based seats.
        Member::forChurch($church->id)
            ->archived()
            ->whereHas('departments')
            ->with('departments')
            ->orderBy('id')
            ->chunkById(100, function (Collection $members) use ($church, $rules, &$scanned, &$removed) {
                foreach ($members as $member) {
                    $scanned++;
                    $result = $this->syncMemberDepartments($church, $member, $rules);
                    $removed += $result['removed'];
                }
            });

        $ageRules = $this->ageOnlyRules($rules);

        if ($ageRules !== []) {
            MemberDependant::forChurch($church->id)
                ->where('relationship', DependantRelationship::Child)
                ->whereNull('linked_member_id')
                ->with('departments')
                ->orderBy('id')
                ->chunkById(100, function (Collection $dependants) use ($church, $ageRules, &$scanned, &$matched, &$attached, &$removed) {
                    foreach ($dependants as $dependant) {
                        $scanned++;
                        $result = $this->syncDependantDepartments($church, $dependant, $ageRules);
                        $attached += $result['attached'];
                        $removed += $result['removed'];

                        if ($result['attached'] > 0 || $result['matched']) {
                            $matched++;
                        }
                    }
                });
        }

        return [
            'scanned' => $scanned,
            'matched' => $matched,
            'attached' => $attached,
            'removed' => $removed,
            'enabled' => true,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     * @return array{attached: int, removed: int, matched: bool}
     */
    private function syncMemberDepartments(Church $church, Member $member, array $rules): array
    {
        $matchingIds = $this->matchingDepartmentIds($member, $rules, $this->memberAge($member), $this->personGender($member), $this->activeLeadershipPositions($member));
        $matchingIds = $this->filterDepartmentIdsForBranch($church, $matchingIds, $member->branch_id);
        $attached = $this->attachMemberToDepartments($church, $member, $matchingIds, autoAssigned: true);
        $removed = $this->reconcileMemberDepartments($member, $rules, $matchingIds);

        return [
            'attached' => $attached,
            'removed' => $removed,
            'matched' => $matchingIds !== [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     * @return array{attached: int, removed: int, matched: bool}
     */
    private function syncDependantDepartments(Church $church, MemberDependant $dependant, array $rules): array
    {
        $dependant->loadMissing('member:id,branch_id');
        $matchingIds = $this->matchingDepartmentIds($dependant, $rules, $this->dependantAge($dependant), $this->personGender($dependant), []);
        $matchingIds = $this->filterDepartmentIdsForBranch($church, $matchingIds, $dependant->member?->branch_id);
        $attached = $this->attachDependantToDepartments($church, $dependant, $matchingIds);
        $removed = $this->reconcileDependantDepartments($dependant, $matchingIds);

        return [
            'attached' => $attached,
            'removed' => $removed,
            'matched' => $matchingIds !== [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     */
    private function attachMatchingDependantDepartments(Church $church, MemberDependant $dependant, array $rules): int
    {
        $dependant->loadMissing('member:id,branch_id');
        $matchingIds = $this->matchingDepartmentIds(
            $dependant,
            $rules,
            $this->dependantAge($dependant),
            $this->personGender($dependant),
            [],
        );
        $matchingIds = $this->filterDepartmentIdsForBranch($church, $matchingIds, $dependant->member?->branch_id);

        return $this->attachDependantToDepartments($church, $dependant, $matchingIds);
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     * @param  list<string>  $activePositions
     * @return array<int, int>
     */
    private function matchingDepartmentIds(
        Member|MemberDependant $person,
        array $rules,
        ?int $age,
        ?string $gender,
        array $activePositions,
    ): array {
        $departmentIds = [];

        foreach ($rules as $rule) {
            if (! is_array($rule)) {
                continue;
            }

            // Dependants only match child-capable rules (no leadership; must include age).
            if ($person instanceof MemberDependant && ! $this->isDependantEligibleRule($rule)) {
                continue;
            }

            if (! $this->ruleMatches($rule, $age, $gender, $activePositions)) {
                continue;
            }

            $departmentId = (int) ($rule['department_id'] ?? 0);

            if ($departmentId > 0) {
                $departmentIds[$departmentId] = $departmentId;
            }
        }

        return $departmentIds;
    }

    /**
     * @param  array<int, int>  $departmentIds
     */
    private function attachMemberToDepartments(Church $church, Member $member, array $departmentIds, bool $autoAssigned): int
    {
        if ($departmentIds === []) {
            return 0;
        }

        $departments = Department::forChurch($church->id)
            ->whereIn('id', array_values($departmentIds))
            ->where('status', DepartmentStatus::Active)
            ->get();

        $attached = 0;

        foreach ($departments as $department) {
            $attached += $this->departmentService->attachMembers($department, [$member->id], $autoAssigned);
        }

        return $attached;
    }

    /**
     * @param  array<int, int>  $departmentIds
     */
    private function attachDependantToDepartments(Church $church, MemberDependant $dependant, array $departmentIds): int
    {
        if ($departmentIds === []) {
            return 0;
        }

        $departments = Department::forChurch($church->id)
            ->whereIn('id', array_values($departmentIds))
            ->where('status', DepartmentStatus::Active)
            ->get();

        $attached = 0;

        foreach ($departments as $department) {
            $attached += $this->departmentService->attachDependants($department, [$dependant->id], true);
        }

        return $attached;
    }

    /**
     * @param  array<int, int>  $departmentIds
     * @return array<int, int>
     */
    private function filterDepartmentIdsForBranch(Church $church, array $departmentIds, ?int $branchId): array
    {
        if ($departmentIds === []) {
            return [];
        }

        return Department::forChurch($church->id)
            ->whereIn('id', array_values($departmentIds))
            ->where('branch_id', $branchId)
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(int) $id => (int) $id])
            ->all();
    }

    /**
     * Remove members who no longer qualify for age/leadership-based departments,
     * and remove auto-assigned memberships that no longer match.
     *
     * @param  list<array<string, mixed>>  $rules
     * @param  array<int, int>  $matchingIds
     */
    private function reconcileMemberDepartments(Member $member, array $rules, array $matchingIds): int
    {
        $removed = 0;
        $childCapableDepartmentIds = $this->departmentIdsForDependantEligibleRules($rules);
        $leadershipDepartmentIds = $this->departmentIdsForLeadershipRules($rules);

        $current = $member->relationLoaded('departments')
            ? $member->departments
            : $member->departments()->get();

        foreach ($current as $department) {
            $departmentId = (int) $department->id;
            $autoAssigned = (bool) ($department->pivot->auto_assigned ?? false);
            $stillMatches = isset($matchingIds[$departmentId]);

            if ($stillMatches) {
                continue;
            }

            $isChildCapableDepartment = isset($childCapableDepartmentIds[$departmentId]);
            $isLeadershipDepartment = isset($leadershipDepartmentIds[$departmentId]);

            // Age/leadership departments must mirror current eligibility (not leftover manual seats).
            // Other departments: only remove auto-assigned rows.
            if ($isChildCapableDepartment || $isLeadershipDepartment || $autoAssigned) {
                $department->members()->detach($member->id);
                $removed++;

                if ((int) $department->head_id === (int) $member->id) {
                    $department->update(['head_id' => null]);
                }
            }
        }

        return $removed;
    }

    /**
     * @param  array<int, int>  $matchingIds
     */
    private function reconcileDependantDepartments(MemberDependant $dependant, array $matchingIds): int
    {
        $removed = 0;

        $current = $dependant->relationLoaded('departments')
            ? $dependant->departments
            : $dependant->departments()->get();

        foreach ($current as $department) {
            if (isset($matchingIds[(int) $department->id])) {
                continue;
            }

            $department->dependants()->detach($dependant->id);
            $removed++;
        }

        return $removed;
    }

    private function isEnabled(Church $church): bool
    {
        return (bool) $this->churchSettingsService->get($church, 'department_assignment_enabled', false);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rules(Church $church): array
    {
        $rules = $this->churchSettingsService->get($church, 'department_assignment_rules', []);

        return is_array($rules) ? array_values($rules) : [];
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     * @return list<array<string, mixed>>
     */
    private function ageOnlyRules(array $rules): array
    {
        return array_values(array_filter(
            $rules,
            fn ($rule) => is_array($rule) && $this->isDependantEligibleRule($rule)
        ));
    }

    /**
     * Child/dependant rules: no leadership requirement, must include an age range.
     * Optional gender filter is allowed (e.g. girls 0–12).
     */
    private function isDependantEligibleRule(array $rule): bool
    {
        $positions = $this->rulePositions($rule);
        $minAge = $this->nullableInt(Arr::get($rule, 'min_age'));
        $maxAge = $this->nullableInt(Arr::get($rule, 'max_age'));

        return $positions === [] && $minAge !== null && $maxAge !== null;
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     * @return array<int, int>
     */
    private function departmentIdsForDependantEligibleRules(array $rules): array
    {
        $ids = [];

        foreach ($this->ageOnlyRules($rules) as $rule) {
            $id = (int) ($rule['department_id'] ?? 0);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return $ids;
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     * @return array<int, int>
     */
    private function departmentIdsForLeadershipRules(array $rules): array
    {
        $ids = [];

        foreach ($rules as $rule) {
            if (! is_array($rule) || $this->rulePositions($rule) === []) {
                continue;
            }

            $id = (int) ($rule['department_id'] ?? 0);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return $ids;
    }

    private function memberAge(Member $member): ?int
    {
        if (! $member->date_of_birth) {
            return null;
        }

        $dob = $member->date_of_birth instanceof Carbon
            ? $member->date_of_birth->copy()->startOfDay()
            : Carbon::parse($member->date_of_birth)->startOfDay();

        return $dob->age;
    }

    private function dependantAge(MemberDependant $dependant): ?int
    {
        return $dependant->age();
    }

    private function personGender(Member|MemberDependant $person): ?string
    {
        $gender = strtolower(trim((string) ($person->gender ?? '')));

        return in_array($gender, ['male', 'female'], true) ? $gender : null;
    }

    /**
     * @param  list<string>  $activePositions
     */
    private function ruleMatches(array $rule, ?int $age, ?string $gender, array $activePositions): bool
    {
        $minAge = $this->nullableInt(Arr::get($rule, 'min_age'));
        $maxAge = $this->nullableInt(Arr::get($rule, 'max_age'));
        $positions = $this->rulePositions($rule);
        $genders = $this->ruleGenders($rule);

        $hasAgeRequirement = $minAge !== null || $maxAge !== null;
        $hasPositionRequirement = $positions !== [];
        $hasGenderRequirement = $genders !== [];

        if (! $hasAgeRequirement && ! $hasPositionRequirement && ! $hasGenderRequirement) {
            return false;
        }

        if ($hasGenderRequirement) {
            if ($gender === null || ! in_array($gender, $genders, true)) {
                return false;
            }
        }

        if ($hasAgeRequirement) {
            if ($minAge === null || $maxAge === null || $age === null) {
                return false;
            }

            if ($age < $minAge || $age > $maxAge) {
                return false;
            }
        }

        if ($hasPositionRequirement) {
            if ($activePositions === []) {
                return false;
            }

            if (array_intersect($positions, $activePositions) === []) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function rulePositions(array $rule): array
    {
        return array_values(array_filter(
            Arr::wrap($rule['leadership_positions'] ?? []),
            fn ($value) => is_string($value) && $value !== ''
        ));
    }

    /**
     * @return list<string>
     */
    private function ruleGenders(array $rule): array
    {
        return array_values(array_unique(array_filter(
            Arr::wrap($rule['genders'] ?? []),
            fn ($value) => in_array($value, ['male', 'female'], true)
        )));
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @return list<string>
     */
    private function activeLeadershipPositions(Member $member): array
    {
        $leaders = $member->relationLoaded('leaders')
            ? $member->leaders->where('is_active', true)
            : $member->leaders()->where('is_active', true)->get();

        return $leaders
            ->map(function ($leader) {
                $position = $leader->position;

                return $position instanceof LeadershipPosition
                    ? $position->value
                    : (string) $position;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
