<?php

namespace OHMedia\SecurityBundle\Command;

use OHMedia\SecurityBundle\Interfaces\CleanerInterface;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BoilerplateCommand extends Command
{
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
        $this->inputConfig = new InputConfiguration();

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ohmedia:security:boilerplate')
            ->setDescription('Command to create the files needed for an entity')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $className = $this->io->ask(sprintf(
            'Class name of the entity to create (e.g. <fg=yellow>%s</>)',
            Str::asClassName(Str::getRandomTerm())
        ));

        if (!$className) {
            $this->io->error('Please provide the class name');

            return Command::INVALID;
        }

        $lockable = $this->io->confirm('Add Lockable functionality?');

        $entityDetails = $this->generator->createClassNameDetails(
            $className,
            'Entity'
        );

        $repositoryDetails = $this->generator->createClassNameDetails(
            $className,
            'Repository',
            'Repository'
        );

        $formDetails = $this->generator->createClassNameDetails(
            $className,
            'Form',
            'Type'
        );

        $providerDetails = $this->generator->createClassNameDetails(
            $className,
            'Provider',
            'Provider'
        );

        $controllerDetails = $this->generator->createClassNameDetails(
            $className,
            'Controller',
            'Controller'
        );

        $voterDetails = $this->generator->createClassNameDetails(
            $className,
            'Security\Voter',
            'Voter'
        );

        // routing

        return Command::SUCCESS;
    }
}
