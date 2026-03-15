<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class BaseCrudController extends AbstractCrudController
{
    protected static string $entityLabelSingular = '';
    protected static string $entityLabelPlural = '';
    protected static array $defaultSort = ['id' => 'DESC'];

    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(10)
            ->setDefaultSort(static::$defaultSort);

        if (static::$entityLabelSingular) {
            $crud->setEntityLabelInSingular(static::$entityLabelSingular);
        }
        if (static::$entityLabelPlural) {
            $crud->setEntityLabelInPlural(static::$entityLabelPlural);
        }

        return $crud;
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
}
