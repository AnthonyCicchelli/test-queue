<?php
declare(strict_types=1);

namespace Assessment\SimpleQueue\Logger;

use Magento\Framework\Logger\Monolog;

class Logger extends Monolog
{
    public function __construct(
        Handler $systemHandler
    ) {
        parent::__construct('assessment_simple_queue', [$systemHandler]);
    }
}
