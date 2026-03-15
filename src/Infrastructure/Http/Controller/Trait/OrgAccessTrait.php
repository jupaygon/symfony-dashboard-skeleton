<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Trait;

use App\Domain\Model\Organization;
use App\Domain\Model\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

trait OrgAccessTrait
{
    /** @return int[]|null null means super admin (no restriction) */
    private function getAllowedOrgIds(): ?array
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return null;
        }

        /** @var User $user */
        $user = $this->getUser();
        $ids = [];
        foreach ($user->getOrganizations() as $org) {
            $ids[] = $org->getId();
        }

        return $ids;
    }

    private function denyUnlessOrgAccess(Organization $organization): void
    {
        $orgIds = $this->getAllowedOrgIds();

        if ($orgIds !== null && !in_array($organization->getId(), $orgIds, true)) {
            throw new AccessDeniedHttpException('Access denied: you do not belong to this organization.');
        }
    }

    private function denyUnlessUserSharesOrg(User $targetUser): void
    {
        $orgIds = $this->getAllowedOrgIds();

        if ($orgIds === null) {
            return;
        }

        foreach ($targetUser->getOrganizations() as $org) {
            if (in_array($org->getId(), $orgIds, true)) {
                return;
            }
        }

        throw new AccessDeniedHttpException('Access denied: you do not share an organization with this user.');
    }
}
