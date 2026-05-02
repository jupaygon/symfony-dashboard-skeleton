<?php

declare(strict_types=1);

namespace App\Application\Service;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Enforces the password policy on plaintext input before hashing.
 *
 * Requirements:
 *   - At least 12 characters
 *   - Score >= STRONG on Symfony's `PasswordStrength` (zxcvbn-style estimator)
 *   - Not present in the haveibeenpwned breach corpus (skipOnError so the API
 *     being unreachable does not block legitimate users)
 */
final readonly class PasswordPolicy
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /** Throws BadRequestHttpException with a user-facing message if the password is unacceptable. */
    public function assertAcceptable(string $plainPassword): void
    {
        $violations = $this->validator->validate($plainPassword, [
            new NotBlank(),
            new Length(min: 12),
            new PasswordStrength(minScore: PasswordStrength::STRENGTH_STRONG),
            new NotCompromisedPassword(skipOnError: true),
        ]);

        if (count($violations) === 0) {
            return;
        }

        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = (string) $violation->getMessage();
        }

        throw new BadRequestHttpException(implode(' ', $messages));
    }
}
