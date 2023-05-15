<?= "<?php\n" ?>

namespace App\Command;

use App\Entity\<?= $singular['pascal_case'] ?>;
use App\Repository\<?= $singular['pascal_case'] ?>Repository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCreateCommand extends Command
{
    private $passwordHasher;
    private $<?= $singular['camel_case'] ?>Repository;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        <?= $singular['pascal_case'] ?>Repository $<?= $singular['camel_case'] ?>Repository
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this-><?= $singular['camel_case'] ?>Repository = $<?= $singular['camel_case'] ?>Repository;

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

        if (!$email || !$password) {
            $this->io->error('Please provide an email and password');

            return Command::INVALID;
        }

        $developer = $this->io->confirm('Flag this user as a developer');

        // if you need to populate more fields, ask for them here
        // or simply provide sensible defaults below

        $<?= $singular['camel_case'] ?> = new <?= $singular['pascal_case'] ?>();

        $hashedPassword = $this->passwordHasher->hashPassword(
            $<?= $singular['camel_case'] ?>,
            $password
        );

        $<?= $singular['camel_case'] . "\n" ?>
            ->setEmail($email)
            ->setPassword($hashedPassword)
            ->setDeveloper($developer)
            ->setEnabled(true)

            // ... set other fields as needed
        ;

        try {
            $this-><?= $singular['camel_case'] ?>Repository->save($<?= $singular['camel_case'] ?>, true);
        } catch(Exception $e) {
            $this->io->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->io->success(sprintf('User %s created successfully', $email));

        return Command::SUCCESS;
    }
}
