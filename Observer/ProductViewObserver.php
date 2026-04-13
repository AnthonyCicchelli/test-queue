<?php

/**
 * Author: Anthony Cicchelli
 * Date: 2026-04-12
 */

declare(strict_types=1);

namespace Assessment\SimpleQueue\Observer;

use Assessment\SimpleQueue\Model\MessagePublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductViewObserver implements ObserverInterface
{
    public function __construct(
        private readonly MessagePublisher $messagePublisher
    ) {
    }

    public function execute(Observer $observer): void
    {
        $this->messagePublisher->execute();
    }
}
