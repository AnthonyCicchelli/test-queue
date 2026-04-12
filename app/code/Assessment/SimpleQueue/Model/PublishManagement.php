<?php
declare(strict_types=1);

namespace Assessment\SimpleQueue\Model;

use Assessment\SimpleQueue\Api\PublishManagementInterface;

class PublishManagement implements PublishManagementInterface
{
    public function __construct(
        private readonly MessagePublisher $messagePublisher
    ) {
    }

    public function execute(): string
    {
        $this->messagePublisher->execute();

        return 'OK';
    }
}
