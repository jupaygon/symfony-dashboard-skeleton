<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Service\BrandContext;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'app_admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly BrandContext $brandContext,
    ) {
    }

    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        $brand = $this->brandContext->get();

        return Dashboard::new()
            ->setTitle($brand->getName())
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin')
            ->setLocales(['en' => 'English', 'es' => 'Español']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Management');
        yield MenuItem::linkTo(OrganizationCrudController::class, 'Organizations', 'fas fa-building');
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fas fa-users');
        yield MenuItem::section();
        yield MenuItem::linkToLogout('Logout', 'fa fa-sign-out');
    }

    public function configureAssets(): Assets
    {
        $brand = $this->brandContext->get();

        return Assets::new()
            ->addCssFile('css/common-dashboard.css')
            ->addCssFile(sprintf('brands/%s/css/custom-dashboard.css', $brand->getKey()));
    }
}
