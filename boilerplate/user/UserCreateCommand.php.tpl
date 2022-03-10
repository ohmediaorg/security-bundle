<?php

namespace App\Command;

use App\Provider\__PASCALCASE__Provider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCreateCommand extends Command
{
    private $__CAMELCASE__Provider;
    private $passwordHasher;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        __PASCALCASE__Provider $__CAMELCASE__Provider
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this->__CAMELCASE__Provider = $__CAMELCASE__Provider;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:user:create')
            ->setDescription('A command to create users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $email = $this->io->ask('Email');

        $password = $this->io->askHidden('Password (hidden)');

        $developer = $this->io->confirm('Is this user a developer?');

        // if you need to populate more fields, ask for them here
        // or simply provide sensible defaults below

        $__CAMELCASE__ = $this->__CAMELCASE__Provider->create();

        $hashedPassword = $this->passwordHasher->hashPassword(
            $__CAMELCASE__,
            $password
        );

        $__CAMELCASE__
            ->setEmail($email)
            ->setPassword($hashed)
            ->setDeveloper($developer)
            ->setEnabled(true)

            // ... set other fields as needed
        ;

        $this->__CAMELCASE__Provider->save($__CAMELCASE__);

        return Command::SUCCESS;
    }
}
