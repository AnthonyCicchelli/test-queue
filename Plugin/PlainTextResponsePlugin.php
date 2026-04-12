<?php

/**
 * Author: Anthony Cicchelli
 * Date: 2026-04-12
 */

declare(strict_types=1);

namespace Assessment\SimpleQueue\Plugin;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response;

class PlainTextResponsePlugin
{
    private const ROUTE_PATH = '/V1/simple-queue/publish';

    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * @param array|int|string|bool|float|null $outputData
     */
    public function aroundPrepareResponse(
        Response $subject,
        callable $proceed,
        $outputData = null
    ): Response {
        if ($outputData === 'OK' && rtrim((string) $this->request->getPathInfo(), '/') === self::ROUTE_PATH) {
            $subject->setMimeType('text/plain');
            $subject->setBody('OK');

            return $subject;
        }

        return $proceed($outputData);
    }
}
