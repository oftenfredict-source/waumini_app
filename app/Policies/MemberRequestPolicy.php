<?php

namespace App\Policies;

use App\Models\MemberRequest;
use App\Models\User;
use App\Services\Church\MemberRequestService;

class MemberRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('member_requests.view');
    }

    public function view(User $user, MemberRequest $memberRequest): bool
    {
        if ($memberRequest->church_id !== $user->church_id) {
            return false;
        }

        if ($user->hasLinkedMember()) {
            return $user->member_id === $memberRequest->member_id;
        }

        if ($user->can('member_requests.view')) {
            return true;
        }

        return app(MemberRequestService::class)->userHandlesRequest($user, $memberRequest);
    }

    public function create(User $user): bool
    {
        return $user->canAccessMemberPortal();
    }

    public function respond(User $user, MemberRequest $memberRequest): bool
    {
        if ($memberRequest->church_id !== $user->church_id) {
            return false;
        }

        if ($user->can('member_requests.manage')) {
            return true;
        }

        return app(MemberRequestService::class)->userHandlesRequest($user, $memberRequest);
    }

    public function downloadCertificate(User $user, MemberRequest $memberRequest): bool
    {
        if ($memberRequest->church_id !== $user->church_id) {
            return false;
        }

        if (! $memberRequest->type->generatesCertificate()) {
            return false;
        }

        // Member portal: only after staff releases the certificate.
        if ($user->hasLinkedMember() && $user->member_id === $memberRequest->member_id && ! $user->can('member_requests.manage')) {
            return $memberRequest->hasDownloadableCertificate();
        }

        if ($user->can('member_requests.view') || $this->respond($user, $memberRequest)) {
            return true;
        }

        return $memberRequest->hasDownloadableCertificate() && $this->view($user, $memberRequest);
    }

    public function previewCertificate(User $user, MemberRequest $memberRequest): bool
    {
        if (! $memberRequest->type->generatesCertificate()) {
            return false;
        }

        return $this->respond($user, $memberRequest) || $user->can('member_requests.view');
    }

    public function releaseCertificate(User $user, MemberRequest $memberRequest): bool
    {
        if (! $memberRequest->type->generatesCertificate()) {
            return false;
        }

        return $this->respond($user, $memberRequest);
    }
}
