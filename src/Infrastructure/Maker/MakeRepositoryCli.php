<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Maker;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Class MakeRepositoryCli.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
#[AsCommand(
    name: 'devscast:make:repository',
    description: 'create a new repository class',
)]
#[AsTaggedItem('console.command')]
class MakeRepositoryCli extends AbstractMakeCli
{
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument('domain', InputArgument::OPTIONAL, 'The domain of the command class (e.g. <fg=yellow>Mailing</>)')
            ->addArgument('entity', InputArgument::OPTIONAL, 'The entity class (e.g. <fg=yellow>Newsletter</>)');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->askDomain($input);
        $this->askArgument($input, 'entity');
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $entity */
        $entity = $input->getArgument('entity');
        $domain = is_string($input->getArgument('domain')) ? $input->getArgument('domain') : null;

        if ($entity === null) {
            $entities = $this->findFiles(
                path: "{$this->projectDir}/src/Domain/{$domain}/Entity",
                suffix: '.php'
            );

            $this->io->text(sprintf('Found %d entities in domain %s', count($entities), $domain));
            $confirm = $this->io->confirm('Do you want to create repositories for all entities?', false);

            if ($confirm && count($entities) > 0) {
                foreach ($entities as $entity) {
                    $makeRepositoryCli = $this->getApplication()?->find('devscast:make:repository');
                    $makeRepositoryCli?->run(new ArrayInput([
                        'domain' => $domain,
                        'entity' => $entity,
                        '--force' => $input->getOption('force'),
                    ]), $output);
                }
            }
        } else {
            $repositoryInterfaceName = sprintf('%sRepositoryInterface', $entity);
            $repositoryClassName = sprintf('%sRepository', $entity);
            $entityClassName = sprintf('%s', $entity);

            if ($entityClassName !== '') {
                $this->createFile(
                    template: 'repository_interface.php',
                    params: [
                        'entityClassName' => $entityClassName,
                        'repositoryInterfaceName' => $repositoryInterfaceName === 'RepositoryInterface' ? false : $repositoryInterfaceName,
                        'domain' => $domain,
                    ],
                    output: "src/Domain/{$domain}/Repository/{$repositoryInterfaceName}.php",
                    force: $input->getOption('force') !== false
                );

                $this->createFile(
                    template: 'repository.php',
                    params: [
                        'entityClassName' => $entityClassName,
                        'repositoryInterfaceName' => $repositoryInterfaceName === 'RepositoryInterface' ? false : $repositoryInterfaceName,
                        'repositoryClassName' => $repositoryClassName,
                        'domain' => $domain,
                    ],
                    output: "src/Infrastructure/{$domain}/Doctrine/Repository/{$repositoryClassName}.php",
                    force: $input->getOption('force') !== false
                );

                $this->io->text(sprintf('RepositoryInterface %s successfully created', $repositoryInterfaceName));
                $this->io->text(sprintf('Repository %s successfully created', $repositoryClassName));
            }
        }

        return Command::SUCCESS;
    }
}
