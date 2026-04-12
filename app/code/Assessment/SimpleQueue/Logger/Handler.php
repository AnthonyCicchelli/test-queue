<?php
declare(strict_types=1);

namespace Assessment\SimpleQueue\Logger;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    protected $fileName = '/var/log/consumer.log';

    public function __construct(
        File $filesystem
    ) {
        parent::__construct($filesystem);
    }
}
