<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Crud\Admin;

use App\Domain\Model\Organization;
use App\Domain\Model\User;
use App\Infrastructure\Http\Controller\Crud\BaseCrudController;
use App\Infrastructure\Http\Controller\Trait\OrgAccessTrait;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @extends BaseCrudController<User> */
class UserCrudController extends BaseCrudController
{
    use OrgAccessTrait;

    protected static string $entityLabelSingular = 'Entity.User.Singular';
    protected static string $entityLabelPlural = 'Entity.User.Plural';
    protected static array $defaultSort = ['name' => 'ASC'];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $impersonate = Action::new('impersonate', false, 'fa-solid fa-user-secret')
            ->setHtmlAttributes(['title' => 'Impersonate'])
            ->linkToUrl(function (User $user): string {
                $target = in_array('ROLE_ADMIN', $user->getRoles(), true) ? '/admin' : '/dashboard';
                return $target . '?_switch_user=' . $user->getEmail();
            })
            ->displayIf(function (User $user): bool {
                return !$user->isSuperAdmin()
                    && $user !== $this->getUser();
            });

        $actions = parent::configureActions($actions);

        return $actions
            ->add(Crud::PAGE_INDEX, $impersonate)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->displayIf(fn(User $user) => !$user->isSuperAdmin() || $this->isGranted('ROLE_SUPER_ADMIN'));
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->displayIf(fn(User $user) => !$user->isSuperAdmin() || $this->isGranted('ROLE_SUPER_ADMIN'));
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->displayIf(fn(User $user) => !$user->isSuperAdmin());
            });
    }

    public function configureFields(string $pageName): iterable
    {
        $orgIds = $this->getAllowedOrgIds();

        yield BooleanField::new('active')->setColumns(2);
        yield FormField::addRow();
        yield TextField::new('name', 'Fields.FullName')->setColumns(4);
        yield FormField::addRow();
        yield EmailField::new('email')->setColumns(4);
        yield FormField::addRow();
        yield TextField::new('password', 'Fields.Password')
            ->onlyWhenCreating()
            ->setColumns(4)
            ->setHelp('Fields.PasswordHelp');
        yield TextField::new('plainPassword', 'Fields.NewPassword')
            ->onlyWhenUpdating()
            ->setColumns(4)
            ->setRequired(false)
            ->setHelp('Fields.NewPasswordHelp');
        yield FormField::addRow();

        $roleChoices = [
            'Admin' => 'ROLE_ADMIN',
            'User' => 'ROLE_USER',
        ];
        $roleBadges = [
            'ROLE_ADMIN' => 'danger',
            'ROLE_USER' => 'primary',
        ];
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $roleChoices = ['Super Admin' => 'ROLE_SUPER_ADMIN'] + $roleChoices;
            $roleBadges['ROLE_SUPER_ADMIN'] = 'dark';
        }

        yield ChoiceField::new('roles')
            ->setColumns(4)
            ->setChoices($roleChoices)
            ->allowMultipleChoices()
            ->renderAsBadges($roleBadges);
        yield FormField::addRow();

        $orgField = AssociationField::new('organizations')
            ->setColumns(4)
            ->formatValue(function ($value, User $entity) use ($orgIds) {
                $orgs = $entity->getOrganizations();
                if ($orgIds !== null) {
                    $orgs = $orgs->filter(fn(Organization $o) => in_array($o->getId(), $orgIds, true));
                }
                $badges = $orgs->map(
                    fn(Organization $o) => sprintf('<span class="badge badge-info">%s</span>', htmlspecialchars($o->getName()))
                )->toArray();

                return implode(' ', $badges);
            });

        if ($orgIds !== null) {
            $orgField->setQueryBuilder(function (QueryBuilder $qb) use ($orgIds) {
                $qb->andWhere('entity.id IN (:orgIds)')
                    ->setParameter('orgIds', $orgIds);
            });
        }

        yield $orgField;
        yield FormField::addRow();
        yield DateTimeField::new('createdAt')->hideOnForm();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $orgIds = $this->getAllowedOrgIds();

        // Non-super-admins: only see users in their orgs, and never see super admins
        if ($orgIds !== null) {
            $qb->innerJoin('entity.organizations', 'orgs')
                ->andWhere('orgs.id IN (:orgIds)')
                ->setParameter('orgIds', $orgIds)
                ->andWhere('entity.roles NOT LIKE :superAdmin')
                ->setParameter('superAdmin', '%ROLE_SUPER_ADMIN%');
        }

        // Role-priority ordering (replaces default sort)
        $qb->resetDQLPart('orderBy')
            ->addSelect("CASE
                WHEN entity.roles LIKE '%ROLE_SUPER_ADMIN%' THEN 0
                WHEN entity.roles LIKE '%ROLE_ADMIN%' THEN 1
                ELSE 2
            END AS HIDDEN role_priority")
            ->orderBy('role_priority', 'ASC')
            ->addOrderBy('entity.name', 'ASC');

        return $qb;
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        $this->denyAccessToSuperAdmin();
        $this->denyAccessUnlessSharedOrg();

        return parent::detail($context);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $this->denyAccessToSuperAdmin();
        $this->denyAccessUnlessSharedOrg();

        return parent::edit($context);
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $hashed = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setPassword($hashed);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->isSuperAdmin() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedHttpException('Only super admins can edit super admin users.');
        }

        if ($entityInstance instanceof User && $entityInstance->getPlainPassword()) {
            $hashed = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword());
            $entityInstance->setPassword($hashed);
        }

        if ($entityInstance instanceof User) {
            $this->preserveHiddenOrganizations($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->isSuperAdmin()) {
            throw new AccessDeniedHttpException('Super admin users cannot be deleted.');
        }

        if ($entityInstance instanceof User) {
            $this->denyUnlessUserSharesOrg($entityInstance);
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    private function denyAccessToSuperAdmin(): void
    {
        $entity = $this->getContext()?->getEntity()?->getInstance();

        if ($entity instanceof User && $entity->isSuperAdmin() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedHttpException('Only super admins can access super admin users.');
        }
    }

    private function denyAccessUnlessSharedOrg(): void
    {
        $entity = $this->getContext()?->getEntity()?->getInstance();

        if ($entity instanceof User) {
            $this->denyUnlessUserSharesOrg($entity);
        }
    }

    private function preserveHiddenOrganizations(User $user): void
    {
        $orgIds = $this->getAllowedOrgIds();

        if ($orgIds === null) {
            return;
        }

        $em = $this->container->get('doctrine')->getManager();
        $originalOrgs = $em->getRepository(Organization::class)->findBy([
            'id' => $em->getConnection()->fetchFirstColumn(
                'SELECT organization_id FROM user_organization WHERE user_id = ?',
                [$user->getId()]
            ),
        ]);

        foreach ($originalOrgs as $org) {
            if (!in_array($org->getId(), $orgIds, true)) {
                $user->addOrganization($org);
            }
        }
    }
}
