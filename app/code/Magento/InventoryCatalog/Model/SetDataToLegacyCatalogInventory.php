<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockStatus;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Set Qty and status for legacy CatalogInventory Stock Status and Stock Item DB tables.
 */
class SetDataToLegacyCatalogInventory
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SetDataToLegacyStockItem
     */
    private $setDataToLegacyStockItem;

    /**
     * @var SetDataToLegacyStockStatus
     */
    private $setDataToLegacyStockStatus;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param SetDataToLegacyStockStatus $setDataToLegacyStockStatus
     * @param IsProductSalableInterface $isProductSalable
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        SetDataToLegacyStockStatus $setDataToLegacyStockStatus,
        IsProductSalableInterface $isProductSalable,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->setDataToLegacyStockStatus = $setDataToLegacyStockStatus;
        $this->isProductSalable = $isProductSalable;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return void
     */
    public function execute(array $sourceItems): void
    {
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                continue;
            }
            $this->setDataToLegacyStockItem->execute(
                $sourceItem->getSku(),
                (float)$sourceItem->getQuantity(),
                (int)$sourceItem->getStatus()
            );
            $this->setDataToLegacyStockStatus->execute(
                $sourceItem->getSku(),
                (float)$sourceItem->getQuantity(),
                (int)$sourceItem->getStatus()
            );
            /**
             * We need to call setDataToLegacyStockStatus second time because we don't have On Save re-indexation
             * as cataloginventory_stock_item table updated with plane SQL queries
             * Thus, initially we put the raw data there, and after that persist the calculated value
             */
            $this->setDataToLegacyStockStatus->execute(
                $sourceItem->getSku(),
                (float)$sourceItem->getQuantity(),
                (int)$this->isProductSalable->execute($sourceItem->getSku(), $this->defaultStockProvider->getId())
            );
        }
    }
}
