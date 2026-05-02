<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Trait;

use App\Domain\Model\Organization;
use App\Domain\Model\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Uid\Uuid;

trait OrgAccessTrait
{
    /** @return Uuid[]|null null means super admin (no restriction) */
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

    /**
     * Binary form for `setParameter('orgIds', $bin, ArrayParameterType::BINARY)`
     * — required when binding into `WHERE id IN (:orgIds)` against `BINARY(16)` columns.
     *
     * @return string[]|null null means super admin (no restriction)
     */
    private function getAllowedOrgIdsBinary(): ?array
    {
        $ids = $this->getAllowedOrgIds();

        if ($ids === null) {
            return null;
        }

        return array_map(static fn(Uuid $id): string => $id->toBinary(), $ids);
    }

    private function denyUnlessOrgAccess(Organization $organization): void
    {
        $orgIds = $this->getAllowedOrgIds();

        if ($orgIds === null) {
            return;
        }

        $targetId = $organization->getId();
        foreach ($orgIds as $allowed) {
            if ($allowed->equals($targetId)) {
                return;
            }
        }

        throw new AccessDeniedHttpException('Access denied: you do not belong to this organization.');
    }

    private function denyUnlessUserSharesOrg(User $targetUser): void
    {
        $orgIds = $this->getAllowedOrgIds();

        if ($orgIds === null) {
            return;
        }

        foreach ($targetUser->getOrganizations() as $org) {
            $orgId = $org->getId();
            foreach ($orgIds as $allowed) {
                if ($allowed->equals($orgId)) {
                    return;
                }
            }
        }

        throw new AccessDeniedHttpException('Access denied: you do not share an organization with this user.');
    }
}
