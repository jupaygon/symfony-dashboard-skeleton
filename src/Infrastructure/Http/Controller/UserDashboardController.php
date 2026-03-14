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

#[AdminDashboard(routePath: '/dashboard', routeName: 'app_dashboard')]
class UserDashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly BrandContext $brandContext,
    ) {
    }

    public function index(): Response
    {
        return $this->render('dashboard/user.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        $brand = $this->brandContext->get();

        return Dashboard::new()
            ->setTitle('<div class="sidebar-logo"></div>')
            ->setFaviconPath('images/logo.svg');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('User Dashboard', 'fa fa-home');

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section();
            yield MenuItem::linkToRoute('Admin Dashboard', 'fa fa-cogs', 'app_admin');
        }

        yield MenuItem::section();
        yield MenuItem::linkToLogout('Logout', 'fa fa-sign-out');
    }

    public function configureAssets(): Assets
    {
        $brand = $this->brandContext->get();

        return Assets::new()
            ->addCssFile(sprintf('brands/%s/css/skin.css', $brand->getKey()))
            ->addCssFile('css/easyadmin-overrides.css');
    }
}
