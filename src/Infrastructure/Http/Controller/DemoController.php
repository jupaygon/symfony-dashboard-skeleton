<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Service\DemoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DemoController extends AbstractController
{
    public function __construct(
        private readonly DemoService $demoService,
    ) {
    }

    #[Route('/demo', name: 'demo_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $demos = $this->demoService->findAll();

        return $this->json(array_map(
            fn($demo) => ['id' => $demo->getId(), 'name' => $demo->getName()],
            $demos,
        ));
    }
}
