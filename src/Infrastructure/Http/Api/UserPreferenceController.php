<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Api;

use App\Application\Service\UserPreferenceService;
use App\Domain\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UserPreferenceController extends AbstractController
{
    public function __construct(
        private readonly UserPreferenceService $preferenceService,
    ) {
    }

    #[Route('/api/user/preference/toggle', name: 'api_user_preference_toggle', methods: ['POST'])]
    public function toggle(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $field = $data['field'] ?? '';

        try {
            $newValue = $this->preferenceService->toggle($user, $field);

            return $this->json(['status' => 'ok', 'field' => $field, 'value' => $newValue]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
