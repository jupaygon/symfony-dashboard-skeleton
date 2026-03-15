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
            ->setTitle('<div class="sidebar-logo"></div>')
            ->setFaviconPath('images/logo.svg')
            ->setTranslationDomain('admin')
            ->setLocales(['en' => 'English', 'es' => 'Español']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Admin Dashboard', 'fa fa-home');
        yield MenuItem::section('Management');
        yield MenuItem::linkTo(OrganizationCrudController::class, 'Organizations', 'fas fa-building');
        yield MenuItem::linkTo(UserCrudController::class, 'Users', 'fas fa-users');
        yield MenuItem::section();
        yield MenuItem::linkToRoute('User Dashboard', 'fa fa-tachometer-alt', 'app_dashboard');
        yield MenuItem::linkToLogout('Logout', 'fa fa-sign-out');
    }

    public function configureAssets(): Assets
    {
        $brand = $this->brandContext->get();

        $assets = Assets::new()
            ->addCssFile(sprintf('brands/%s/css/skin.css', $brand->getKey()));

        if ($brand->getKey() !== 'default') {
            $assets->addCssFile('css/easyadmin-overrides.css');
        }

        if ($brand->isSidebarCollapsed()) {
            $assets->addCssFile('css/sidebar-collapsed.css');
        }

        return $assets;
    }
}
