<?php

/**
 * Author: Anthony Cicchelli
 * Date: 2026-04-12
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Assessment_SimpleQueue',
    __DIR__
);
