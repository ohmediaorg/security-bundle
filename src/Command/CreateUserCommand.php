<?php

namespace OHMedia\SecurityBundle\Command;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('ohmedia:security:create-user')
            ->setDescription('A command to create users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email');

        $password = $io->askHidden('Password (hidden)');

        if (!$email || !$password) {
            $io->error('Please provide an email and password');

            return Command::INVALID;
        }

        $developer = $io->confirm('Flag this user as a developer');

        $user = new User();

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );

        $user
            ->setEmail($email)
            ->setPassword($hashedPassword)
            ->setDeveloper($developer)
            ->setEnabled(true)
        ;

        try {
            $this->userRepository->save($user, true);
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('User %s created successfully', $email));

        return Command::SUCCESS;
    }
}
