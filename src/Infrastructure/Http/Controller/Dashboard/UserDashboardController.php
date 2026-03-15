<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Dashboard;

use App\Application\Service\BrandContext;
use App\Application\Service\UserPreferenceService;
use App\Domain\Model\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/dashboard', routeName: 'app_dashboard')]
class UserDashboardController extends AbstractDashboardController
{
    /** @param array<string, array{code: string, name: string}> $languages */
    public function __construct(
        private readonly BrandContext $brandContext,
        private readonly UserPreferenceService $preferenceService,
        #[Autowire('%app.languages%')] private readonly array $languages,
    ) {
    }

    public function index(): Response
    {
        return $this->render('dashboard/user.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        $brand = $this->brandContext->get();

        $dashboard = Dashboard::new()
            ->setTitle('<div class="sidebar-logo"></div>')
            ->setFaviconPath(sprintf('resources/brands/%s/images/logos/logo.svg', $brand->getKey()))
            ->setTranslationDomain('messages')
            ->setLocales(array_combine(
                array_column($this->languages, 'code'),
                array_column($this->languages, 'name'),
            ));

        if ($brand->getKey() !== 'default') {
            $dashboard->disableDarkMode();
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if ($user && $this->preferenceService->get($user, 'content_maximized')) {
            $dashboard->renderContentMaximized();
        }

        return $dashboard;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Menu.UserDashboard', 'fa fa-home');

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section();
            yield MenuItem::linkToRoute('Menu.AdminDashboard', 'fa fa-cogs', 'app_admin');
        }

        if (!$this->brandContext->get()->isTopnav()) {
            yield MenuItem::section();
            yield MenuItem::linkToLogout('Menu.Logout', 'fa fa-sign-out');
        }
    }

    public function configureAssets(): Assets
    {
        $brand = $this->brandContext->get();

        $assets = Assets::new()
            ->addCssFile(sprintf('brands/%s/css/skin.css', $brand->getKey()));

        if (!in_array($brand->getKey(), ['default', 'topnav'], true)) {
            $assets->addCssFile('css/easyadmin-overrides.css');
        }

        if ($brand->isTopnav()) {
            $assets->addCssFile('css/topnav-layout.css');
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$brand->isTopnav() && $user && $this->preferenceService->get($user, 'sidebar_collapsed')) {
            $assets->addCssFile('css/sidebar-collapsed.css');
        }

        return $assets;
    }
}
