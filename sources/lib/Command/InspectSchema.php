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
use PommProject\Foundation\ResultIterator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * InspectSchema
 *
 * Inspector from the command line.
 *
 * @package   Cli
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       SchemaAwareCommand
 */
class InspectSchema extends SchemaAwareCommand
{
    /**
     * configure
     *
     * @see Command
     */
    public function configure(): void
    {
        $this
            ->setName('pomm:inspect:schema')
            ->setDescription('Show relations in a given schema.')
            ;

        parent::configure();
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
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $session = $this->mustBeModelManagerSession($this->getSession());
        $info = $session
            ->getInspector()
            ->getSchemaRelations($this->fetchSchemaOid()
        );
        $this->formatOutput($output, $info);

        return 0;
    }

    /**
     * formatOutput
     *
     * Format result
     *
     * @access protected
     * @param  OutputInterface $output
     * @param  ResultIterator  $info
     */
    protected function formatOutput(OutputInterface $output, ResultIterator $info): void
    {
        $output->writeln(
            sprintf(
                "Found <info>%d</info> relations in schema <info>'%s'</info>.",
                $info->count(),
                $this->schema
            )
        );
        $table = (new Table($output))
            ->setHeaders(['name', 'type', 'oid ', 'comment'])
            ;

        foreach ($info as $table_info) {
            $table->addRow([
                sprintf("<fg=yellow>%s</fg=yellow>", $table_info['name']),
                $table_info['type'],
                $table_info['oid'],
                !is_null($table_info['comment']) ? wordwrap($table_info['comment']) : '',
            ]);
        }

        $table->render();
    }
}
