<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class PlainLineFormatter
{
    /**
     * Customize the given logger instance so that each log entry is written
     * as a single, unadorned line: just the message, no level/channel/date
     * prefix from Monolog. The mail log format is fully specified by the
     * caller (including its own German-formatted timestamp), so Monolog's
     * own framing would only duplicate/clash with it.
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter("%message%\n"));
        }
    }
}
