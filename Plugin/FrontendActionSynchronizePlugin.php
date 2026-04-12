<?php

/**
 * Author: Anthony Cicchelli
 * Date: 2026-04-12
 */

declare(strict_types=1);

namespace Assessment\SimpleQueue\Plugin;

use Assessment\SimpleQueue\Model\MessagePublisher;
use Magento\Catalog\Controller\Product\Frontend\Action\Synchronize;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;

class FrontendActionSynchronizePlugin
{
    private const TYPE_ID = 'recently_viewed_product';

    public function __construct(
        private readonly MessagePublisher $messagePublisher
    ) {
    }

    public function afterExecute(
        Synchronize $subject,
        ResultInterface $result
    ): ResultInterface {
        $request = $subject->getRequest();
        if (!$request instanceof RequestInterface) {
            return $result;
        }

        if ($request->getParam('type_id') !== self::TYPE_ID) {
            return $result;
        }

        $ids = $request->getParam('ids', []);
        if (!is_array($ids) || !$this->hasProductId($ids)) {
            return $result;
        }

        $this->messagePublisher->execute();

        return $result;
    }

    /**
     * @param array<mixed> $ids
     */
    private function hasProductId(array $ids): bool
    {
        foreach ($ids as $item) {
            if (is_array($item) && isset($item['product_id']) && is_numeric((string) $item['product_id'])) {
                return true;
            }
        }

        return false;
    }
}
