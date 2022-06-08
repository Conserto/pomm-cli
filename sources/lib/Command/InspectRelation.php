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
use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\Exception\FoundationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * InspectRelation
 *
 * Display information about a given relation.
 *
 * @package   Cli
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       SchemaAwareCommand
 */
class InspectRelation extends RelationAwareCommand
{
    protected ?int $relation_oid = null;

    /**
     * configure
     *
     * @see Command
     */
    protected function configure(): void
    {
        $this
            ->setName('pomm:inspect:relation')
            ->setDescription('Display a relation information.')
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
        $this->relation = $input->getArgument('relation');

        $session = $this->mustBeModelManagerSession($this->getSession());
        $this->relation_oid = $session
            ->getInspector()
            ->getTableOid($this->schema, $this->relation)
            ;

        if ($this->relation_oid === null) {
            throw new CliException(
                sprintf(
                    "Relation <comment>%s.%s</comment> not found.",
                    $this->schema,
                    $this->relation
                )
            );
        }

        $fields_infos = $session
            ->getInspector()
            ->getTableFieldInformation($this->relation_oid)
            ;

        $this->formatOutput($output, $fields_infos);

        return 0;
    }

    /**
     * formatOutput
     *
     * Render output.
     *
     * @access protected
     * @param  OutputInterface         $output
     * @param  ConvertedResultIterator $fields_infos
     */
    protected function formatOutput(OutputInterface $output, ConvertedResultIterator $fields_infos): void
    {
        $output->writeln(sprintf("Relation <fg=cyan>%s.%s</fg=cyan>", $this->schema, $this->relation));
        $table = (new Table($output))
            ->setHeaders(['pk', 'name', 'type', 'default', 'notnull', 'comment'])
            ;

        foreach ($fields_infos as $info) {
            $table->addRow(
                [
                    $info['is_primary'] ? '<fg=cyan>*</fg=cyan>' : '',
                    sprintf("<fg=yellow>%s</fg=yellow>", $info['name']),
                    $this->formatType($info['type']),
                    $info['default'],
                    $info['is_notnull'] ? 'yes' : 'no',
                    !is_null($info['comment']) ? wordwrap($info['comment']) : '',
                ]
            );
        }

        $table->render();
    }

    /**
     * formatType
     *
     * Format type.
     *
     * @access protected
     * @param string $type
     * @return string
     */
    protected function formatType(string $type): string
    {
        if (preg_match('/^(?:(.*)\.)?_(.*)$/', $type, $matches)) {
            if ($matches[1] !== '') {
                return sprintf("%s.%s[]", $matches[1], $matches[2]);
            } else {
                return $matches[2].'[]';
            }
        } else {
            return $type;
        }
    }
}
