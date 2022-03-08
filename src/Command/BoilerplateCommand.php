<?php

namespace OHMedia\SecurityBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Symfony\Component\String\u;

class BoilerplateCommand extends Command
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->templateDir = __DIR__ . '/../../boilerplate/';
        $this->find = [];
        $this->replace = [];

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
        $this->io = new SymfonyStyle($input, $output);

        $className = $this->io->ask('Class name of the entity to create');

        if (!$className) {
            $this->io->error('Please provide the class name');

            return Command::INVALID;
        }

        if ($input->getOption('user')) {
            $extend = 'EntityUser';
            $extendUse = 'OHMedia\SecurityBundle\Entity\User as EntityUser';
        }
        else {
            $extend = 'Entity';
            $extendUse = 'OHMedia\SecurityBundle\Entity\Entity';
        }

        $camelCase = u($className)->camel();
        $snakeCase = u($className)->snake();
        $pascalCase = u($camelCase)->title();
        $readable = u($snakeCase)->replace('_', ' ');

        $findReplace = [
            '__ENTITYEXTEND' => $extend,
            '__ENTITYEXTENDUSE' => $extendUse,
            '__CAMELCASE__' => $camelCase,
            '__SNAKECASE__' => $snakeCase,
            '__PASCALCASE__' => $pascalCase,
            '__READABLE__' => $readable,
        ];

        $this->find = array_keys($findReplace);
        $this->replace = array_values($findReplace);

        $entityFile = sprintf('src/Entity/%s.php', $pascalCase);
        $repositoryFile = sprintf('src/Repository/%sRepository.php', $pascalCase);
        $formFile = sprintf('src/Form/%sType.php', $pascalCase);
        $providerFile = sprintf('src/Provider/%sProvider.php', $pascalCase);
        $controllerFile = sprintf('src/Controller/%sController.php', $pascalCase);
        $routesFile = sprintf('config/routes/%s.yaml', $snakeCase);
        $voterFile = sprintf('src/Security/Voter/%sVoter.php', $pascalCase);

        $this
            ->generateFile('Entity.php.tpl', $entityFile)
            ->generateFile('Repository.php.tpl', $repositoryFile)
            ->generateFile('Form.php.tpl', $formFile)
            ->generateFile('Provider.php.tpl', $providerFile)
            ->generateFile('Controller.php.tpl', $controllerFile)
            ->generateFile('routes.yaml.tpl', $routesFile)
            ->generateFile('Voter.php.tpl', $voterFile)
        ;

        return Command::SUCCESS;
    }

    private function generateFile(string $template, string $destination)
    {
        $contents = str_replace(
            $this->find,
            $this->replace,
            file_get_contents($this->templateDir . $template)
        );

        file_put_contents($this->projectDir . $destination, $contents);

        return $this;
    }
}
