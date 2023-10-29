<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Maker;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Class MakeControllerCli.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
#[AsCommand(
    name: 'ddd:make:controller',
    description: 'Create a new crud controller class',
)]
#[AsTaggedItem('console.command')]
class MakeControllerCli extends AbstractMakeCli
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

        /** @var string $domain */
        $domain = $input->getArgument('domain');
        $this->askClass($input, 'entity', "Domain/${domain}/Entity/*");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $domain */
        $domain = $input->getArgument('domain');

        /** @var string $entity */
        $entity = $input->getArgument('entity');

        if ($input->getArgument('entity') === null) {
            $entities = $this->findFiles(
                path: "{$this->projectDir}/src/Domain/{$domain}/Entity",
                suffix: '.php'
            );

            $this->io->text(sprintf('Found %d entities in domain %s', count($entities), $domain));
            $confirm = $this->io->confirm('Do you want to create controllers for all entities?', false);

            if ($confirm && count($entities) > 0) {
                foreach ($entities as $entity) {
                    $this->createController(
                        entity: $entity,
                        domain: $domain,
                        force: $input->getOption('force') !== false
                    );
                }
            }
        } else {
            $this->createController(
                entity: $entity,
                domain: $domain,
                force: $input->getOption('force') !== false
            );
        }

        return Command::SUCCESS;
    }

    private function createController(string $entity, string $domain, bool $force): void
    {
        $this->createFile(
            template: 'controller_crud.php',
            params: [
                'entityClassName' => $entity,
                'domain' => $domain,
            ],
            output: "src/Infrastructure/{$domain}/Symfony/Controller/Admin/{$entity}Controller.php",
            force: $force
        );
        $this->io->text(sprintf('%sController successfully created', $entity));
    }
}
