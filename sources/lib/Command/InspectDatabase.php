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
use PommProject\Foundation\Exception\FoundationException;
use PommProject\Foundation\ResultIterator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * InspectDatabase
 *
 * Return the list of schemas in the current database.
 *
 * @package   Cli
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       PommAwareCommand
 */
class InspectDatabase extends SessionAwareCommand
{
    /**
     * configure
     *
     * @see Command
     */
    public function configure(): void
    {
        $this
            ->setName('pomm:inspect:database')
            ->setDescription('Show schemas in the current database.')
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
     * @throws FoundationException
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $session = $this->mustBeModelManagerSession($this->getSession());
        $info = $session->getInspector()->getSchemas();
        $this->formatOutput($output, $info);

        return 0;
    }

    /**
     * formatOutput
     *
     * Format command output from the inspector's result.
     *
     * @access protected
     * @param OutputInterface $output
     * @param ResultIterator $iterator
     * @return void
     */
    protected function formatOutput(OutputInterface $output, ResultIterator $iterator): void
    {
        $output->writeln(
            sprintf(
                "Found <info>%d</info> schemas in database.",
                $iterator->count()
            )
        );
        $table = (new Table($output))
            ->setHeaders(['name', 'oid ', 'relations', 'comment'])
            ;

        foreach ($iterator as $schema_info) {
            $table->addRow([
                sprintf("<fg=yellow>%s</fg=yellow>", $schema_info['name']),
                $schema_info['oid'],
                $schema_info['relations'],
                wordwrap((string) $schema_info['comment'])
            ]);
        }

        $table->render();
    }
}
