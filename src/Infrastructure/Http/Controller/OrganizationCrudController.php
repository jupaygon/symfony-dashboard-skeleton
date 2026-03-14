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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

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
        // Tab: General Information
        yield FormField::addTab('General Information', 'fas fa-info-circle');
        yield BooleanField::new('active')->setColumns(12);
        yield FormField::addRow();
        yield TextField::new('name')->setColumns(4);
        yield UrlField::new('web')->setColumns(4)->hideOnIndex();
        yield FormField::addRow();
        yield TextField::new('legalName', 'Legal Name')->setColumns(4)->hideOnIndex();
        yield TextField::new('vatNumber', 'VAT Number')->setColumns(4)->hideOnIndex();

        // Tab: Address
        yield FormField::addTab('Address', 'fas fa-map-marker-alt');
        yield TextField::new('address')->setColumns(4);
        yield TextField::new('city')->setColumns(2);
        yield FormField::addRow();
        yield TextField::new('state')->setColumns(4);
        yield TextField::new('zip')->setColumns(2);
        yield TextField::new('country')->setColumns(4);

        // Tab: Team
        yield FormField::addTab('Team', 'fas fa-users');
        yield IntegerField::new('teamCount', 'Members')->onlyOnIndex();
        yield AssociationField::new('users', 'Team Members')->hideOnIndex();

        // Tab: Notes
        yield FormField::addTab('Notes', 'fas fa-sticky-note');
        yield TextareaField::new('comments')->setColumns(6)->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnForm();
    }
}
