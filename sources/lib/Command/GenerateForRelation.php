<?php
/*
 * This file is part of Pomm's Cli package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Cli\Command;

use PommProject\Cli\Exception\CliException;
use PommProject\Cli\Exception\GeneratorException;
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\ParameterHolder;
use PommProject\ModelManager\Generator\EntityGenerator;
use PommProject\ModelManager\Generator\ModelGenerator;
use PommProject\ModelManager\Generator\StructureGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * GenerateForRelation
 *
 * Generate a Structure, a model and an entity class if they do not already
 * exist (unless --force is specified).
 *
 * @package   Cli
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ModelGenerator
 */
class GenerateForRelation extends RelationAwareCommand
{
    /**
     * configure
     *
     * @see Command
     */
    public function configure(): void
    {
        $this
            ->setName('pomm:generate:relation-all')
            ->setDescription('Generate structure, model and entity file for a given relation.')
            ;
        parent::configure();
        $this
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Force overwriting existing files.'
            )
            ->addOption(
                'psr4',
                null,
                InputOption::VALUE_NONE,
                'Use PSR4 structure.'
            )
        ;
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws CliException
     * @throws GeneratorException
     * @throws FoundationException
     * @throws \PommProject\ModelManager\Exception\GeneratorException
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $session = $this->mustBeModelManagerSession($this->getSession());

        $this->updateOutput(
            $output,
            (new StructureGenerator(
                $session,
                $this->schema,
                $this->relation,
                $this->getPathFile($input->getArgument('config-name'), $this->relation, null, 'AutoStructure', $input->getOption('psr4')),
                $this->getNamespace($input->getArgument('config-name'), 'AutoStructure')
            ))->generate(new ParameterHolder(array_merge($input->getArguments(), $input->getOptions())))
        );

        $pathFile = $this->getPathFile($input->getArgument('config-name'), $this->relation, 'Model', '', $input->getOption('psr4'));
        if (!file_exists($pathFile) || $input->getOption('force')) {
            $this->updateOutput(
                $output,
                (new ModelGenerator(
                    $session,
                    $this->schema,
                    $this->relation,
                    $pathFile,
                    $this->getNamespace($input->getArgument('config-name'))
                ))->generate(new ParameterHolder(array_merge($input->getArguments(), $input->getOptions())))
            );
        } elseif ($output->isVerbose()) {
            $this->writelnSkipFile($output, $pathFile, 'model');
        }

        $pathFile = $this->getPathFile($input->getArgument('config-name'), $this->relation, '', '', $input->getOption('psr4'));
        if (!file_exists($pathFile) || $input->getOption('force')) {
            $this->updateOutput(
                $output,
                (new EntityGenerator(
                    $session,
                    $this->schema,
                    $this->relation,
                    $pathFile,
                    $this->getNamespace($input->getArgument('config-name')),
                    $this->flexible_container
                ))->generate(new ParameterHolder(array_merge($input->getArguments(), $input->getOptions())))
            );
        } elseif ($output->isVerbose()) {
            $this->writelnSkipFile($output, $pathFile, 'entity');
        }

        return 0;
    }

    /**
     * writelnSkipFile
     *
     * Write an informative message
     *
     * @access private
     * @param  OutputInterface $output
     * @param  string          $pathFile
     * @param  null|string     $file_type
     */
    private function writelnSkipFile(OutputInterface $output, string $pathFile, ?string $file_type = null): void
    {
        $file_type = $file_type === null ? '' : sprintf("%s ", $file_type);

        $output->writeln(
            sprintf(
                " <fg=red>✗</fg=red>  <fg=blue>Preserving</fg=blue> existing %sfile <fg=yellow>'%s'</fg=yellow>.",
                $file_type,
                $pathFile
            )
        );
    }
}
