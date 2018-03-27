<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Exception\InputException;

/**
 * Provides all product SKUs by ProductIds. Key is product id, value is sku
 * @api
 */
interface GetSkusByProductIdsInterface
{
    /**
     * @param array $productIds
     * @return array
     * @throws InputException
     */
    public function execute(array $productIds): array;
}
