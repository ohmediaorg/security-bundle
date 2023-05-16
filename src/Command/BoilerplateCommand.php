<?php

namespace OHMedia\SecurityBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Inflector\EnglishInflector;

use function Symfony\Component\String\u;

class BoilerplateCommand extends Command
{
    private string $templateDir;
    private string $projectDir;
    private Filesystem $filesystem;
    private EnglishInflector $inflector;
    private SymfonyStyle $io;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir . '/';
        $this->templateDir = __DIR__ . '/../../boilerplate/';
        $this->filesystem = new Filesystem();

        $this->inflector = new EnglishInflector();

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ohmedia:security:boilerplate')
            ->setDescription('Command to create the files needed for an entity')
            ->addOption('user', null, InputOption::VALUE_NONE, 'Specify that this is the user entity')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isUser = $input->getOption('user');

        $this->io = new SymfonyStyle($input, $output);

        if ($isUser) {
            $className = 'User';
        }
        else {
            $className = $this->io->ask('Class name of the entity');
        }

        if (!$className) {
            $this->io->error('Please provide the class name');

            return Command::INVALID;
        }

        $singular = $this->inflector->singularize($className)[0];
        $plural = $this->inflector->pluralize($singular)[0];

        $parameters = [
            'singular' => $this->generateParameters($singular),
            'plural' => $this->generateParameters($plural),
            'is_user' => $isUser,
        ];

        $pascalCase = $parameters['singular']['pascal_case'];

        $parameters['has_view_route'] = $this->io->confirm(sprintf(
            'Does the %s entity require a view route? (eg. /%s/{id})',
            $pascalCase,
            $parameters['singular']['kebab_case']
        ), false);

        $entityFile = sprintf('src/Entity/%s.php', $pascalCase);
        $repositoryFile = sprintf('src/Repository/%sRepository.php', $pascalCase);
        $formFile = sprintf('src/Form/%sType.php', $pascalCase);
        $controllerFile = sprintf('src/Controller/%sController.php', $pascalCase);
        $indexVoterFile = sprintf('src/Security/Voter/%s/%sIndexVoter.php', $pascalCase, $pascalCase);

        $this
            ->generateFile('Entity.tpl.php', $entityFile, $parameters)
            ->generateFile('Repository.tpl.php', $repositoryFile, $parameters)
            ->generateFile('Form.tpl.php', $formFile, $parameters)
            ->generateFile('Controller.tpl.php', $controllerFile, $parameters)
            ->generateFile('IndexVoter.tpl.php', $indexVoterFile, $parameters)
        ;

        $cruds = ['Create', 'Edit', 'Delete'];

        if ($parameters['has_view_route']) {
            $cruds [] = 'View';
        }

        foreach ($cruds as $crud) {
            $crudVoterFile = sprintf(
                'src/Security/Voter/%s/%s%sVoter.php',
                $pascalCase,
                $pascalCase,
                $crud
            );

            $parameters['crud'] = $crud;

            $this->generateFile('CrudVoter.tpl.php', $crudVoterFile, $parameters);
        }

        if ($isUser) {
            $this
                ->generateFile(
                    'UserCreateCommand.tpl.php',
                    'src/Command/UserCreateCommand.php',
                    $parameters
                )
            ;
        }

        return Command::SUCCESS;
    }

    private function generateParameters(string $word)
    {
        $camelCase = u($word)->camel();
        $snakeCase = u($word)->snake();
        $pascalCase = u($camelCase)->title();
        $kebabCase = u($snakeCase)->replace('_', '-');
        $readable = u($snakeCase)->replace('_', ' ');

        return [
            'camel_case' => $camelCase,
            'snake_case' => $snakeCase,
            'pascal_case' => $pascalCase,
            'kebab_case' => $kebabCase,
            'readable' => $readable,
        ];
    }

    private function generateFile(string $template, string $destination, array $parameters)
    {
        $absoluteDestination = $this->projectDir . $destination;

        if (file_exists($absoluteDestination)) {
            $continue = $this->io->confirm(sprintf(
                'The destination file <fg=yellow>%s</> exists. Do you want to overwrite it?',
                $destination
            ), false);

            if (!$continue) {
                return $this;
            }
        }

        ob_start();

        extract($parameters);

        include $this->templateDir . $template;

        $contents = ob_get_clean();

        $this->filesystem->mkdir(\dirname($absoluteDestination));

        file_put_contents($absoluteDestination, $contents);

        $this->io->success(sprintf('Generated %s', $destination));

        return $this;
    }
}
