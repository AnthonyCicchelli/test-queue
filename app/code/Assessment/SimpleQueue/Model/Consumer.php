<?php
declare(strict_types=1);

namespace Assessment\SimpleQueue\Model;

use Assessment\SimpleQueue\Logger\Logger;

class Consumer
{
    public function __construct(
        private readonly Logger $logger
    ) {
    }

    public function process(string $payload): void
    {
        try {
            /** @var array{datetime?: string} $message */
            $message = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->logger->warning(
                sprintf('Unable to decode message payload: %s', $exception->getMessage())
            );
            return;
        }

        $publishTime = (string) ($message['datetime'] ?? 'unknown');
        $consumedTime = date(DATE_ATOM);

        $this->logger->info(
            sprintf(
                'Message published at %s and consumed at %s',
                $publishTime,
                $consumedTime
            )
        );
    }
}
