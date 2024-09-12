<?php

namespace Langur;

class CLI
{
    public string $argv0;

    /**
     * Run based on arguments provided from the command-line.
     *
     * This may print things to `php://stdout` and/or `php://stderr` depending
     * on the configuration, options, and what happens.
     *
     * @param list<string> $argv
     *
     * @return int Exit code (0 means success, non-zero is failure)
     */
    public function execute(array $argv): int
    {
        /* @var ?Command */
        $command = null;

        $this->argv0 = array_shift($argv) ?? 'langur';

        if (empty($argv)) {
            $this->printUsage();
            return 1;
        }

        try {
            foreach ($argv as $arg) {
                if ($command === null) {
                    if (str_starts_with($arg, '-')) {
                        $this->processGlobalSwitch($arg);
                    } else {
                        $command = $this->getCommand($arg);
                    }
                } else {
                    $command->processSwitch($arg);
                }
            }
        } catch (Exception\BadUsage $exception) {
            fwrite(
                STDERR,
                "Bad argument: " . $exception->getMessage() . PHP_EOL
            );
            $this->printUsage();
            return 1;
        } catch (\Exception $exception) {
            fwrite(
                STDERR,
                $this->argv0 . ": Uncaught exception: " . $exception->getMessage() . PHP_EOL
            );
            return 1;
        }

        if ($command) {
            return $command->execute($this);
        } else {
            fwrite(
                STDERR,
                $this->argv0 . ": no command given" . PHP_EOL
            );
            $this->printUsage();
        }

        return 0;
    }

    public function processGlobalSwitch(string $arg): void
    {
        throw new Exception\BadUsage("didn't understand global switch `{$arg}`");
    }

    /** @var array<string> Map of arguments to our Command objects */
    private array $commandList = [
        'help' => Command\Help::class,
        /*
         * list
         * new
         * up
         * create
         * drop
         * migrate
         * rollback | down
         * status
         * dump
         * load
         * wait
         */
    ];

    public function getCommand(string $arg): Command
    {
        if (array_key_exists($arg, $this->commandList)) {
            /** @var Command $command */
            $command = new ($this->commandList[$arg])();
            return $command;
        } else {
            throw new Exception\BadUsage("didn't understand command `{$arg}`");
        }
    }

    public function printUsage(): void
    {
        fwrite(STDERR, <<<USAGE
            Usage: {$this->argv0} [global-options] command [command-options] ...

            Global options:
                -?, --help  Print this help

            Commands:
                help        Print this help

            Command options:
                -?, --help  Print help for specified command

                Use --help to see additional options and arguments for each command.
            USAGE
        );
        fwrite(STDERR, PHP_EOL . PHP_EOL);
    }
}
