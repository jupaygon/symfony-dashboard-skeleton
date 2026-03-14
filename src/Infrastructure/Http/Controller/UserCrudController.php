<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Domain\Model\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @extends AbstractCrudController<User> */
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(10)
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $impersonate = Action::new('impersonate', false, 'fa-solid fa-user-secret')
            ->setHtmlAttributes(['title' => 'Impersonate'])
            ->linkToUrl(function (User $user): string {
                return '?_switch_user=' . $user->getEmail();
            })
            ->displayIf(function (User $user): bool {
                return !$user->isSuperAdmin();
            });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $impersonate)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-magnifying-glass')->setLabel(false)->setHtmlAttributes(['title' => 'View'])
                    ->displayIf(fn(User $user) => !$user->isSuperAdmin() || $this->isGranted('ROLE_SUPER_ADMIN'));
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-pen-to-square')->setLabel(false)->setHtmlAttributes(['title' => 'Edit'])
                    ->displayIf(fn(User $user) => !$user->isSuperAdmin() || $this->isGranted('ROLE_SUPER_ADMIN'));
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Delete'])
                    ->displayIf(fn(User $user) => !$user->isSuperAdmin());
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addRow();
        yield TextField::new('name', 'Full Name')->setColumns(4);
        yield FormField::addRow();
        yield EmailField::new('email')->setColumns(4);
        yield FormField::addRow();
        yield TextField::new('password')
            ->onlyWhenCreating()
            ->setColumns(4)
            ->setHelp('Plain text — will be hashed automatically');
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
        yield AssociationField::new('organizations')->setColumns(4);
        yield BooleanField::new('active');
        yield DateTimeField::new('createdAt')->hideOnForm();
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $hashed = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setPassword($hashed);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function deleteEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->isSuperAdmin()) {
            throw new AccessDeniedHttpException('Super admin users cannot be deleted.');
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
