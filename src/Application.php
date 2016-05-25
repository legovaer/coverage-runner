<?php

namespace Legovaer\CoverageRunner;

use Legovaer\CoverageRunner\Command\AnalysisCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class Application
 * @package Drupal\Console\Console
 */
class Application extends BaseApplication
{
    /**
     * @var string
     */
    const NAME = 'Coverage Runner';
    /**
     * @var string
     */
    const VERSION = '0.0.1';

    /**
     * @var string
     */
    protected $commandName;

    /**
     * Create a new application extended from \Symfony\Component\Console\Application.
     */
    public function __construct()
    {
        parent::__construct($this::NAME, $this::VERSION);
    }

    /**
     * Returns the long version of the application.
     *
     * @return string The long application version
     *
     * @api
     */
    public function getLongVersion()
    {
        if ('UNKNOWN' !== $this->getName() && 'UNKNOWN' !== $this->getVersion()) {
            return sprintf('<info>'.self::NAME.' '.self::VERSION.'</info>');
        }

        return '<info>Coverage Runner</info>';
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }

    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        // This should return the name of your command.
        return 'analysis:command';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new AnalysisCommand();

        return $defaultCommands;
    }
}
