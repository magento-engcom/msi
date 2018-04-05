<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Controller\Adminhtml\Source;

/**
 * Prepare region data. Specified for form structure
 */
class SourceRegionDataProcessor
{
    /**
     * @param array $data
     * @return array
     */
    public function process(array $data): array
    {
        if (!isset($data['region_id']) || '' === $data['region_id']) {
            $data['region_id'] = null;
        }

        if (null !== $data['region_id'] || !isset($data['region']) || '' === trim($data['region'])) {
            $data['region'] = null;
        }

        return $data;
    }
}
