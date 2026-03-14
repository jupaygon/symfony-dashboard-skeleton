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
            ->setTitle('<img src="/images/logo.svg" style="width:42px;height:42px;vertical-align:middle;margin-right:10px"><span style="font-size:1.3rem;font-weight:700;color:#38bdf8;letter-spacing:0.05em">DS</span>')
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
            ->addCssFile('css/common-dashboard.css')
            ->addCssFile(sprintf('brands/%s/css/custom-dashboard.css', $brand->getKey()));
    }
}
