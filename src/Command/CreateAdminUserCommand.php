<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create an administrator user',
)]
final class CreateAdminUserCommand extends Command
{
    private const EMAIL_PATTERN =
        '/^[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}$/i';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->title('Create administrator');

        $email = $this->askEmail($io);
        $password = $this->askPassword($io);

        $user = new User();
        $user
            ->setEmail($email)
            ->setRole('ROLE_ADMIN')
            ->setCreatedAt(new \DateTimeImmutable());

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password,
        );

        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Administrator "%s" created.',
            $user->getEmail(),
        ));

        return Command::SUCCESS;
    }

    private function askEmail(SymfonyStyle $io): string
    {
        while (true) {
            $email = mb_strtolower(trim(
                (string) $io->ask('Email'),
            ));

            if (!preg_match(self::EMAIL_PATTERN, $email)) {
                $io->error('Invalid email address.');
                continue;
            }

            $existingUser = $this->userRepository->findOneBy([
                'email' => $email,
            ]);

            if ($existingUser !== null) {
                $io->error('A user already exists with this email.');
                continue;
            }

            return $email;
        }
    }

    private function askPassword(SymfonyStyle $io): string
    {
        while (true) {
            $password = (string) $io->askHidden('Password');

            if ($password === '') {
                $io->error('Password cannot be empty.');
                continue;
            }

            if (mb_strlen($password) < 8) {
                $io->error(
                    'Password must contain at least 8 characters.',
                );
                continue;
            }

            return $password;
        }
    }
}
