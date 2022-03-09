<?php

namespace OHMedia\SecurityBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

use function Symfony\Component\String\u;

class BoilerplateCommand extends Command
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir . '/';
        $this->templateDir = __DIR__ . '/../../boilerplate/';
        $this->find = [];
        $this->replace = [];
        $this->filesystem = new Filesystem();

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

        $camelCase = u($className)->camel();
        $snakeCase = u($className)->snake();
        $pascalCase = u($camelCase)->title();
        $kebabCase = u($snakeCase)->replace('_', '-');
        $readable = u($snakeCase)->replace('_', ' ');

        $migrationClass = 'Version' . date('YmdHis', strtotime('+100 years'));

        $findReplace = [
            '__CAMELCASE__' => $camelCase,
            '__SNAKECASE__' => $snakeCase,
            '__PASCALCASE__' => $pascalCase,
            '__KEBABCASE__' => $kebabCase,
            '__READABLE__' => $readable,
            '__MIGRATIONCLASS__' => $migrationClass
        ];

        $this->find = array_keys($findReplace);
        $this->replace = array_values($findReplace);

        if ($isUser) {
            $entityTemplate = 'User.php.tpl';
            $voterTemplate = 'UserVoter.php.tpl';
        }
        else {
            $entityTemplate = 'Entity.php.tpl';
            $voterTemplate = 'Voter.php.tpl';
        }

        $entityFile = sprintf('src/Entity/%s.php', $pascalCase);
        $repositoryFile = sprintf('src/Repository/%sRepository.php', $pascalCase);
        $formFile = sprintf('src/Form/%sType.php', $pascalCase);
        $providerFile = sprintf('src/Provider/%sProvider.php', $pascalCase);
        $controllerFile = sprintf('src/Controller/%sController.php', $pascalCase);
        $routesFile = sprintf('config/routes/%s.yaml', $snakeCase);
        $voterFile = sprintf('src/Security/Voter/%sVoter.php', $pascalCase);

        $this
            ->generateFile($entityTemplate, $entityFile)
            ->generateFile('Repository.php.tpl', $repositoryFile)
            ->generateFile('Form.php.tpl', $formFile)
            ->generateFile('Provider.php.tpl', $providerFile)
            ->generateFile('Controller.php.tpl', $controllerFile)
            ->generateFile('routes.yaml.tpl', $routesFile)
            ->generateFile($voterTemplate, $voterFile)
        ;

        if ($isUser) {
            $migrationFile = sprintf('migrations/%s.php', $migrationClass);

            $this
                ->generateFile(
                    'security/login.html.twig.tpl',
                    'templates/security/login.html.twig'
                )
                ->generateFile(
                    'security/LoginController.php.tpl',
                    'src/Controller/LoginController.php'
                )
                ->generateFile(
                    'security/LoginAuthenticator.php.tpl',
                    'src/Security/LoginAuthenticator.php'
                )
                ->generateFile(
                    'security/Migration.php.tpl',
                    $migrationFile
                )
            ;
        }

        return Command::SUCCESS;
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

        $contents = str_replace(
            $this->find,
            $this->replace,
            file_get_contents($this->templateDir . $template)
        );

        $this->filesystem->mkdir(\dirname($absoluteDestination));

        file_put_contents($absoluteDestination, $contents);

        $this->io->success(sprintf('Generated %s', $destination));

        return $this;
    }
}
