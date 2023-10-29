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
 * Class MakeHandlerCli.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
#[AsCommand(
    name: 'ddd:make:handler',
    description: 'Create a new command handler class',
)]
#[AsTaggedItem('console.command')]
class MakeHandlerCli extends AbstractMakeCli
{
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the command class (e.g. <fg=yellow>SendNewsletterCommand</>)')
            ->addArgument('domain', InputArgument::OPTIONAL, 'The domain of the command class (e.g. <fg=yellow>Mailing</>)')
            ->addArgument('entity', InputArgument::OPTIONAL, 'The entity class (e.g. <fg=yellow>Newsletter</>)');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->askDomain($input);
        $this->askArgument($input, 'entity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $domain */
        $domain = $input->getArgument('domain');

        /** @var string $entity */
        $entity = $input->getArgument('entity');

        /** @var string|null $name */
        $name = $input->getArgument('name');

        if ($name === null) {
            $commands = $this->findFiles(
                path: sprintf('src/Application/%s/Command', $domain),
                suffix: 'Command.php'
            );

            $this->io->text(sprintf('Found %d commands in domain %s', count($commands), $domain));
            $confirm = $this->io->confirm('Do you want to create handlers for all commands?', false);

            if ($confirm && count($commands) > 0) {
                foreach ($commands as $command) {
                    /** @var string $command */
                    $command = str_replace('Command.php', '', $command);

                    /** @var string $entity */
                    $entity = str_replace(['Create', 'Update', 'Delete'], '', $command);

                    $this->createHandler(
                        name: $command,
                        entity: $entity,
                        domain: $domain,
                        force: $input->getOption('force') !== false
                    );
                }
            }
        } else {
            $this->createHandler(
                name: $name,
                entity: $entity,
                domain: $domain,
                force: $input->getOption('force') !== false
            );
        }

        return Command::SUCCESS;
    }

    private function createHandler(string $name, string $entity, string $domain, bool $force): void
    {
        $commandClassName = sprintf('%sCommand', $name);
        $handlerClassName = sprintf('%sHandler', $name);
        $repositoryInterfaceName = sprintf('%sRepositoryInterface', $entity);
        $entityClassName = sprintf('%s', $entity);

        $this->createFile(
            template: 'handler.php',
            params: [
                'commandClassName' => $commandClassName,
                'handlerClassName' => $handlerClassName,
                'entityClassName' => $entityClassName,
                'repositoryInterfaceName' => $repositoryInterfaceName === 'RepositoryInterface' ? false : $repositoryInterfaceName,
                'domain' => $domain,
                'is_create_command' => str_starts_with($commandClassName, 'Create'),
                'is_update_command' => str_starts_with($commandClassName, 'Update'),
                'is_delete_command' => str_starts_with($commandClassName, 'Delete'),
            ],
            output: "src/Application/{$domain}/Handler/{$handlerClassName}.php",
            force: $force !== false
        );
        $this->io->text(sprintf('Handler %s successfully created', $handlerClassName));
    }
}
