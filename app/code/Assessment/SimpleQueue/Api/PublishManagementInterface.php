<?php

/**
 * Author: Anthony Cicchelli
 * Date: 2026-04-12
 */

declare(strict_types=1);

namespace Assessment\SimpleQueue\Api;

interface PublishManagementInterface
{
    /**
     * Publish a simple queue message and return a success marker.
     *
     * @return string
     */
    public function execute(): string;
}
