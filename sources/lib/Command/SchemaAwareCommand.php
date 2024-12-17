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
use PommProject\Foundation\Inflector;
use PommProject\ModelManager\Model\FlexibleEntity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SchemaAwareCommand
 *
 * Base class for generator commands.
 *
 * @abstract
 * @package   Cli
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       PommAwareCommand
 */
abstract class SchemaAwareCommand extends SessionAwareCommand
{
    protected ?string $schema = null;
    protected ?string $prefix_dir = null;
    protected ?string $prefix_ns = null;
    protected ?string $pathFile = null;
    protected ?string $namespace = null;
    protected ?string $flexible_container = null;

    /**
     * configure
     *
     * @see PommAwareCommand
     */
    protected function configureRequiredArguments(): SchemaAwareCommand
    {
        parent::configureRequiredArguments()
            ->addOption(
                'prefix-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Indicate a directory prefix.',
                '.'
            )
            ->addOption(
                'prefix-ns',
                'a',
                InputOption::VALUE_REQUIRED,
                'Indicate a namespace prefix.',
                ''
            )
        ;

        return $this;
    }

    /**
     * configureOptionals
     *
     * @see PommAwareCommand
     */
    protected function configureOptionals(): SchemaAwareCommand
    {
        parent::configureOptionals()
            ->addArgument(
                'schema',
                InputArgument::OPTIONAL,
                'Schema of the relation.',
                'public'
            )
            ->addOption(
                'flexible-container',
                null,
                InputOption::VALUE_REQUIRED,
                'Use an alternative flexible entity container',
                FlexibleEntity::class
            )
        ;

        return $this;
    }
    /**
     * execute
     *
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $this->schema   = $input->getArgument('schema');

        if (!$this->schema) {
            $this->schema = 'public';
        }

        $this->prefix_dir = $input->getOption('prefix-dir');
        $this->prefix_ns  = $input->getOption('prefix-ns');
        $this->flexible_container = $input->getOption('flexible-container');
        return 0;
    }

    /**
     * getPathFile
     *
     * Create path file from parameters and namespace.
     *
     * @access protected
     * @param string $config_name
     * @param string $file_name
     * @param  ?string $file_suffix
     * @param string|null $extra_dir
     * @param bool $format_psr4
     * @return string
     */
    protected function getPathFile(string $config_name, string $file_name, ?string $file_suffix = '', ?string $extra_dir = '', bool $format_psr4 = false): string
    {
        $prefix_ns = "";

        if (!$format_psr4) {
            $prefix_ns = str_replace('\\', '/', trim((string) $this->prefix_ns, '\\'));
        }
        $elements =
            [
                rtrim((string) $this->prefix_dir, '/'),
                $prefix_ns,
                Inflector::studlyCaps($config_name),
                Inflector::studlyCaps(sprintf("%s_schema", $this->schema)),
                $extra_dir,
                sprintf("%s%s.php", Inflector::studlyCaps($file_name), $file_suffix)
            ];

        return implode(
            DIRECTORY_SEPARATOR,
            array_filter($elements, fn(array|string|null $val): bool => $val != null)
        );
    }

    /**
     * getNamespace
     *
     * Create namespace from parameters.
     *
     * @access protected
     * @param  string $config_name
     * @param  string $extra_ns
     * @return string
     */
    protected function getNamespace(string $config_name, string $extra_ns = ''): string
    {
        $elements =
            [
                $this->prefix_ns,
                Inflector::studlyCaps($config_name),
                Inflector::studlyCaps(sprintf("%s_schema", $this->schema)),
                $extra_ns
            ];

        return implode('\\', array_filter($elements, fn(null|string $val): bool => $val != null));
    }

    /**
     * fetchSchemaOid
     *
     * Get the schema Oid from database.
     *
     * @access protected
     * @return int $oid
     * @throws CliException|FoundationException
     */
    protected function fetchSchemaOid(): int
    {
        $session = $this->mustBeModelManagerSession($this->getSession());
        $schema_oid = $session
            ->getInspector()
            ->getSchemaOid($this->schema)
            ;

        if ($schema_oid === null) {
            throw new CliException(
                sprintf(
                    "Could not find schema '%s'.",
                    $this->schema
                )
            );
        }

        return $schema_oid;
    }
}
