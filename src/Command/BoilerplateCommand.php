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
    private array $parameters;

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

        $this->parameters = [
            'singular' => $this->generateCases($singular),
            'plural' => $this->generateCases($plural),
            'is_user' => $isUser,
        ];

        $pascalCase = $this->parameters['singular']['pascal_case'];

        $entityFile = sprintf('src/Entity/%s.php', $pascalCase);
        $repositoryFile = sprintf('src/Repository/%sRepository.php', $pascalCase);
        $formFile = sprintf('src/Form/%sType.php', $pascalCase);
        $controllerFile = sprintf('src/Controller/%sController.php', $pascalCase);
        $voterFile = sprintf('src/Security/Voter/%sVoter.php', $pascalCase);

        $this
            ->generateFile('Entity.tpl.php', $entityFile)
            ->generateFile('Repository.tpl.php', $repositoryFile)
            ->generateFile('Form.tpl.php', $formFile)
            ->generateFile('Controller.tpl.php', $controllerFile)
            ->generateFile('Voter.tpl.php', $voterFile)
        ;

        if ($isUser) {
            $this
                ->generateFile(
                    'UserCreateCommand.tpl.php',
                    'src/Command/UserCreateCommand.php'
                )
            ;
        }

        return Command::SUCCESS;
    }

    private function generateCases(string $word)
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

    private function generateFile(string $template, string $destination)
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

        extract($this->parameters);

        include $this->templateDir . $template;

        $contents = ob_get_clean();

        $this->filesystem->mkdir(\dirname($absoluteDestination));

        file_put_contents($absoluteDestination, $contents);

        $this->io->success(sprintf('Generated %s', $destination));

        return $this;
    }
}
