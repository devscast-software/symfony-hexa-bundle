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
 * Class MakeFormCli.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
#[AsCommand(
    name: 'devscast:make:form',
    description: 'create a new form class',
)]
#[AsTaggedItem('console.command')]
class MakeFormCli extends AbstractMakeCli
{
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'The command class (e.g. <fg=yellow>Newsletter</>)')
            ->addArgument('domain', InputArgument::OPTIONAL, 'The domain of the command class (e.g. <fg=yellow>Mailing</>)');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->askDomain($input);

        /** @var string $domain */
        $domain = $input->getArgument('domain');
        $this->askClass($input, 'name', sprintf('Application/%s/Command/*', $domain));
    }

    /**
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $domain */
        $domain = $input->getArgument('domain');

        /** @var string|null $name */
        $name = $input->getArgument('name');

        if ($name === null) {
            $commands = $this->findFiles(
                path: sprintf('src/Application/%s/Command', $domain),
                suffix: '.php'
            );

            $this->io->text(sprintf('Found %d commands in domain %s', count($commands), $domain));
            $confirm = $this->io->confirm('Do you want to create forms for all commands?', false);

            if ($confirm && count($commands) > 0) {
                foreach ($commands as $command) {
                    if (! str_starts_with('Delete', $command)) {
                        $this->createForm(
                            name: $command,
                            domain: $domain,
                            force: $input->getOption('force') !== false
                        );
                    }
                }
            }
        } else {
            $this->createForm(
                name: $name,
                domain: $domain,
                force: $input->getOption('force') !== false
            );
        }

        return Command::SUCCESS;
    }

    /**
     * @throws \ReflectionException
     */
    private function createForm(string $name, string $domain, bool $force): void
    {
        $commandClassName = sprintf('%s', $name);
        $commandFormClassName = sprintf('%sForm', str_replace('Command', '', $commandClassName));

        /** @var class-string $fqcn */
        $fqcn = sprintf('Application\\%s\\Command\\%s', $domain, $commandClassName);

        $this->createFile(
            template: 'command_form.php',
            params: [
                'commandClassProperties' => $this->getClassProperties(
                    fqcn: $fqcn,
                    ignore: ['_entity', 'created_at', 'updated_at']
                ),
                'commandClassName' => $commandClassName,
                'commandFormClassName' => $commandFormClassName,
                'domain' => $domain,
            ],
            output: "src/Infrastructure/{$domain}/Symfony/Form/{$commandFormClassName}.php",
            force: $force !== false
        );

        $this->io->text(sprintf('Form %s successfully created', $commandFormClassName));
    }
}
