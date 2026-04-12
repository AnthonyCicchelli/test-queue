<?php
declare(strict_types=1);

namespace Assessment\SimpleQueue\Plugin;

class FrontendStorageManagerPlugin
{
    public function afterGetConfigurationJson(
        \Magento\Catalog\Block\FrontendStorageManager $subject,
        string $result
    ): string {
        try {
            $configuration = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $result;
        }

        if (!is_array($configuration) || !isset($configuration['recently_viewed_product'])) {
            return $result;
        }

        if (!is_array($configuration['recently_viewed_product'])) {
            return $result;
        }

        $configuration['recently_viewed_product']['allowToSendRequest'] = 1;

        try {
            return json_encode($configuration, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $result;
        }
    }
}
