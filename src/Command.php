<?php

namespace Langur;

abstract class Command
{
    public function processSwitch(string $arg): void
    {
        throw new Exception\BadUsage("didn't understand switch `{$arg}`");
    }

    abstract public function execute(CLI $cli): int;
}
