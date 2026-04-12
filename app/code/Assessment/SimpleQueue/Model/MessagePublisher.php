<?php

/**
 * Author: Anthony Cicchelli
 * Date: 2026-04-12
 */

declare(strict_types=1);

namespace Assessment\SimpleQueue\Model;

use Magento\Framework\MessageQueue\PublisherInterface;

class MessagePublisher
{
    private const TOPIC_NAME = 'assessment.simple_queue.publish';

    public function __construct(
        private readonly PublisherInterface $publisher
    ) {
    }

    public function execute(): void
    {
        $payload = json_encode(
            ['datetime' => date(DATE_ATOM)],
            JSON_THROW_ON_ERROR
        );

        $this->publisher->publish(self::TOPIC_NAME, $payload);
    }
}
