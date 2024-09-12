<?php

namespace Langur\Command;

use Langur\Command;
use Langur\CLI;

class Help extends Command
{
    public function execute(CLI $cli): int
    {
        $cli->printUsage();
        return 0;
    }
}
