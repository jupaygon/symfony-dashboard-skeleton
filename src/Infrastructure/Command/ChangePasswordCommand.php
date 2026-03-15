<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\Port\UserRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:change-password',
    description: 'Change the password for a user by email'
)]
class ChangePasswordCommand extends Command
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'User email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            $io->error(sprintf('User with email "%s" not found.', $email));

            return Command::FAILURE;
        }

        $password = $io->askHidden('New password');

        if (!$password) {
            $io->error('Password cannot be empty.');

            return Command::FAILURE;
        }

        $confirm = $io->askHidden('Confirm password');

        if ($password !== $confirm) {
            $io->error('Passwords do not match.');

            return Command::FAILURE;
        }

        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);
        $this->userRepository->save($user);

        $io->success(sprintf('Password changed for %s (%s).', $user->getName(), $email));

        return Command::SUCCESS;
    }
}
