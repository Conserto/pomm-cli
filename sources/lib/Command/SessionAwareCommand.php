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
use PommProject\Foundation\Exception\FoundationException as FoundationExceptionAlias;
use PommProject\Foundation\Inspector\InspectorPooler;
use PommProject\Foundation\Session\Session;
use PommProject\ModelManager\Session as ModelManagerSession;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SessionAwareCommand
 *
 * Base command for Cli commands that need a session.
 *
 * @package   Cli
 * @copyright 2014 - 2015 Grégoire HUBERT
 * @author    Grégoire HUBERT
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       Command
 */
class SessionAwareCommand extends PommAwareCommand
{
    private ?Session $session = null;

    protected string $config_name;

    /**
     * execute
     *
     * Set pomm dependent variables.
     *
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output): int|null
    {
        parent::execute($input, $output);
        $this->config_name = $input->getArgument('config-name');
        return null;
    }

    /**
     * configureRequiredArguments
     *
     * In order to keep the same argument order for all commands, it is
     * necessary to be able to declare base required fields before subcommands.
     *
     * @access protected
     * @return SessionAwareCommand $this
     */
    protected function configureRequiredArguments(): SessionAwareCommand
    {
        $this
            ->addArgument(
                'config-name',
                InputArgument::REQUIRED,
                'Database configuration name to open a session.'
            )
            ;

        return $this;
    }

    /**
     * getSession
     *
     * Return a session.
     *
     * @access protected
     * @return Session
     * @throws CliException
     * @throws FoundationExceptionAlias
     */
    protected function getSession(): Session
    {
        if ($this->session === null) {
            $this->session = $this
                ->getPomm()
                ->getSession($this->config_name)
                ->registerClientPooler(new InspectorPooler())
                ;
        }

        return $this->session;
    }

    /**
     * mustBeModelManagerSession
     *
     * Check if a session is a \PommProject\ModelManager\Session.
     *
     * @access protected
     * @param Session $session
     * @return ModelManagerSession
     * @throws GeneratorException
     */
    protected function mustBeModelManagerSession(Session $session): ModelManagerSession
    {
        if (!$session instanceof ModelManagerSession) {
            throw new GeneratorException(
                sprintf(
                    "To generate models, you should use a '\PommProject\ModelManager\Session session' ('%s' used).",
                    $session::class
                )
            );
        }

        return $session;
    }

    /**
     * setSession
     *
     * When testing, it is useful to provide directly the session to be used.
     *
     * @access public
     * @param Session $session
     * @return SessionAwareCommand
     */
    public function setSession(Session $session): SessionAwareCommand
    {
        $this->session = $session;

        return $this;
    }
}
