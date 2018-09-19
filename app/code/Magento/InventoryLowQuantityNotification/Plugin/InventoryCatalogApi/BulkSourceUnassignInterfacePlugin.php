<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\InventoryCatalogApi;

use Magento\InventoryCatalogApi\Api\BulkSourceUnassignInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationUnassign;

class BulkSourceUnassignInterfacePlugin
{
    /**
     * @var BulkConfigurationUnassign
     */
    private $bulkConfigurationUnassign;

    /**
     * @param BulkConfigurationUnassign $bulkConfigurationUnassign
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkConfigurationUnassign $bulkConfigurationUnassign
    ) {
        $this->bulkConfigurationUnassign = $bulkConfigurationUnassign;
    }

    /**
     * @param BulkSourceUnassignInterface $subject
     * @param callable $proceed
     * @param array $skus
     * @param array $sources
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        BulkSourceUnassignInterface $subject,
        callable $proceed,
        array $skus,
        array $sources
    ): int {
        $res = $proceed($skus, $sources);
        $this->bulkConfigurationUnassign->execute($skus, $sources);
        return $res;
    }
}
