<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Crud\Admin;

use App\Domain\Model\Organization;
use App\Infrastructure\Http\Controller\Crud\BaseCrudController;
use App\Infrastructure\Http\Controller\Trait\OrgAccessTrait;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\HttpFoundation\Response;

/** @extends BaseCrudController<Organization> */
class OrganizationCrudController extends BaseCrudController
{
    use OrgAccessTrait;

    protected static string $entityLabelSingular = 'Entity.Organization.Singular';
    protected static string $entityLabelPlural = 'Entity.Organization.Plural';
    protected static array $defaultSort = ['name' => 'ASC'];

    public static function getEntityFqcn(): string
    {
        return Organization::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $orgIdsBin = $this->getAllowedOrgIdsBinary();

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

        $usersField = AssociationField::new('users', 'Fields.TeamMembers')->hideOnIndex();
        if ($orgIdsBin !== null) {
            $usersField->setQueryBuilder(function (QueryBuilder $qb) use ($orgIdsBin) {
                $qb->innerJoin('entity.organizations', 'allowed_orgs')
                    ->andWhere('allowed_orgs.id IN (:orgIds)')
                    ->setParameter('orgIds', $orgIdsBin, ArrayParameterType::BINARY);
            });
        }
        yield $usersField;
        yield DateTimeField::new('createdAt')->hideOnForm();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $orgIdsBin = $this->getAllowedOrgIdsBinary();

        if ($orgIdsBin !== null) {
            $qb->andWhere('entity.id IN (:orgIds)')
                ->setParameter('orgIds', $orgIdsBin, ArrayParameterType::BINARY);
        }

        return $qb;
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        $this->denyAccessUnlessOrgOwned($context);

        return parent::detail($context);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $this->denyAccessUnlessOrgOwned($context);

        return parent::edit($context);
    }

    public function delete(AdminContext $context): KeyValueStore|Response
    {
        $this->denyAccessUnlessOrgOwned($context);

        return parent::delete($context);
    }

    private function denyAccessUnlessOrgOwned(AdminContext $context): void
    {
        $entity = $context->getEntity()->getInstance();

        if ($entity instanceof Organization) {
            $this->denyUnlessOrgAccess($entity);
        }
    }
}
