<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Crud\Admin;

use App\Domain\Model\Organization;
use App\Infrastructure\Http\Controller\Crud\BaseCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/** @extends BaseCrudController<Organization> */
class OrganizationCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Organization::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Entity.Organization.Singular')
            ->setEntityLabelInPlural('Entity.Organization.Plural')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield BooleanField::new('active')->setColumns(2);
        yield FormField::addRow();
        yield TextField::new('name')->setColumns(4);
        yield UrlField::new('web')->setColumns(4)->hideOnIndex();
        yield FormField::addRow();
        yield TextField::new('legalName', 'Fields.LegalName')->setColumns(4)->hideOnIndex();
        yield TextField::new('vatNumber', 'Fields.VatNumber')->setColumns(4)->hideOnIndex();
        yield FormField::addFieldset('Fields.Address', 'fas fa-map-marker-alt')->hideOnIndex();
        yield TextField::new('address')->setColumns(4);
        yield TextField::new('city')->setColumns(2);
        yield FormField::addRow();
        yield TextField::new('state')->setColumns(4);
        yield TextField::new('zip')->setColumns(2);
        yield TextField::new('country')->setColumns(4);
        yield FormField::addFieldset('Notes', 'fas fa-sticky-note')->hideOnIndex();
        yield TextareaField::new('comments')->setColumns(6)->hideOnIndex();
        yield FormField::addFieldset('Team', 'fas fa-users');
        yield IntegerField::new('teamCount', 'Fields.Members')->onlyOnIndex();
        yield AssociationField::new('users', 'Fields.TeamMembers')->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnForm();
    }
}
