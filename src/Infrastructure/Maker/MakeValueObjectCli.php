<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Maker;

use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Class MakeValueObjectCli.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
#[AsCommand(
    name: 'ddd:make:value-object',
    description: 'create a new value object class',
)]
#[AsTaggedItem('console.command')]
class MakeValueObjectCli extends AbstractMakeCli
{
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'The value object class (e.g. <fg=yellow>Username</>)')
            ->addArgument('domain', InputArgument::OPTIONAL, 'The domain of the command class (e.g. <fg=yellow>User</>)')
            ->addArgument('type', InputArgument::OPTIONAL, 'The type of the value object class (e.g. <fg=yellow>string</>)')
            ->addOption('with-choices', null, InputOption::VALUE_OPTIONAL, 'value are already defined', false);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->askDomain($input);
        $this->askArgument($input, 'name');
        $this->askArgument($input, 'type');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $name */
        $name = $input->getArgument('name');

        /** @var string $domain */
        $domain = $input->getArgument('domain');

        $valueObjectVariableName = Str::asSnakeCase($name);
        $valueObjectClassName = Str::asCamelCase($name);

        $this->createFile(
            template: 'value_object.php',
            params: [
                'valueObjectVariableName' => $valueObjectVariableName,
                'valueObjectClassName' => $valueObjectClassName,
                'valueObjectWithChoices' => $input->getOption('with-choices') !== false,
                'domain' => $domain,
            ],
            output: "src/Domain/{$domain}/ValueObject/{$valueObjectClassName}.php",
            force: $input->getOption('force') !== false
        );

        $this->createFile(
            template: 'value_object_type.php',
            params: [
                'valueObjectVariableName' => $valueObjectVariableName,
                'valueObjectClassName' => $valueObjectClassName,
                'valueObjectWithChoices' => $input->getOption('with-choices') !== false,
                'domain' => $domain,
            ],
            output: "src/Infrastructure/{$domain}/Symfony/Form/ValueObject/{$valueObjectClassName}Type.php",
            force: $input->getOption('force') !== false
        );

        $this->createFile(
            template: 'value_object_mapping.xml',
            params: [
                'valueObjectVariableName' => $valueObjectVariableName,
                'valueObjectClassName' => $valueObjectClassName,
                'domain' => $domain,
            ],
            output: "src/Infrastructure/{$domain}/Doctrine/Mapping/{$valueObjectClassName}.orm.xml",
            force: $input->getOption('force') !== false
        );

        $this->io->text(message: sprintf('ValueObject %s successfully created', $valueObjectClassName));
        $this->io->text(message: sprintf('ValueObject Form type %sType successfully created', $valueObjectClassName));
        $this->io->text(message: sprintf('ValueObject Doctrine mapping %s.orm.xml successfully created', $valueObjectClassName));

        return Command::SUCCESS;
    }
}
