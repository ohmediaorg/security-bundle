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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $className = $this->io->ask('Class name of the entity');

        if (!$className) {
            $this->io->error('Please provide the class name');

            return Command::INVALID;
        }

        $singular = $this->inflector->singularize($className)[0];
        $plural = $this->inflector->pluralize($singular)[0];

        $parameters = [
            'singular' => $this->generateParameters($singular),
            'plural' => $this->generateParameters($plural),
        ];

        $pascalCase = $parameters['singular']['pascal_case'];
        $snakeCase = $parameters['singular']['snake_case'];

        $parameters['has_view_route'] = $this->io->confirm(sprintf(
            'Does the %s entity require a view route? (eg. /%s/{id})',
            $pascalCase,
            $parameters['singular']['kebab_case']
        ), false);

        $entityPhpFile = sprintf('src/Entity/%s.php', $pascalCase);
        $repositoryPhpFile = sprintf('src/Repository/%sRepository.php', $pascalCase);
        $formPhpFile = sprintf('src/Form/%sType.php', $pascalCase);
        $controllerPhpFile = sprintf('src/Controller/%sController.php', $pascalCase);
        $voterPhpFile = sprintf('src/Security/Voter/%sVoter.php', $pascalCase);
        $indexTwigFile = sprintf('templates/%s/%s_index.html.twig', $snakeCase, $snakeCase);
        $createTwigFile = sprintf('templates/%s/%s_create.html.twig', $snakeCase, $snakeCase);
        $editTwigFile = sprintf('templates/%s/%s_edit.html.twig', $snakeCase, $snakeCase);
        $formTwigFile = sprintf('templates/%s/_%s_form.html.twig', $snakeCase, $snakeCase);
        $deleteTwigFile = sprintf('templates/%s/%s_delete.html.twig', $snakeCase, $snakeCase);

        $this
            ->generateFile('Entity.tpl.php', $entityPhpFile, $parameters)
            ->generateFile('Repository.tpl.php', $repositoryPhpFile, $parameters)
            ->generateFile('Form.tpl.php', $formPhpFile, $parameters)
            ->generateFile('Controller.tpl.php', $controllerPhpFile, $parameters)
            ->generateFile('Voter.tpl.php', $voterPhpFile, $parameters)
            ->generateFile('twig/index.tpl.php', $indexTwigFile, $parameters)
            ->generateFile('twig/create.tpl.php', $createTwigFile, $parameters)
            ->generateFile('twig/edit.tpl.php', $editTwigFile, $parameters)
            ->generateFile('twig/_form.tpl.php', $formTwigFile, $parameters)
            ->generateFile('twig/delete.tpl.php', $deleteTwigFile, $parameters)
        ;

        if ($parameters['has_view_route']) {
            $viewTwigFile = sprintf('templates/%s/%s_view.html.twig', $snakeCase, $snakeCase);

            $this->generateFile('twig/view.tpl.php', $viewTwigFile, $parameters);
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
