<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Domain\Model\Organization;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Organization> */
class OrganizationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Organization::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Organization')
            ->setEntityLabelInPlural('Organizations')
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(10)
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa-solid fa-magnifying-glass')->setLabel(false)->setHtmlAttributes(['title' => 'View']);
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(false)->setHtmlAttributes(['title' => 'Edit']);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(false)->setHtmlAttributes(['title' => 'Delete']);
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addRow();
        yield TextField::new('name')->setColumns(4);
        yield BooleanField::new('active');
        yield AssociationField::new('users')->hideOnForm();
        yield DateTimeField::new('createdAt')->hideOnForm();
    }
}
