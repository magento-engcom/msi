<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockStatus;
use Magento\InventorySales\Model\GetActualSalesChannel;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Set Qty and status for legacy CatalogInventory Stock Status and Stock Item DB tables,
 * if corresponding MSI SourceItem assigned to Default Source has been saved
 */
class SetDataToLegacyCatalogInventoryAtSourceItemsSavePlugin
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
     * @var GetActualSalesChannel
     */
    private $getActualSalesChannel;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param SetDataToLegacyStockStatus $setDataToLegacyStockStatus
     * @param IsProductSalableInterface $isProductSalable
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param GetActualSalesChannel $getActualSalesChannel
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        SetDataToLegacyStockStatus $setDataToLegacyStockStatus,
        IsProductSalableInterface $isProductSalable,
        DefaultStockProviderInterface $defaultStockProvider,
        GetActualSalesChannel $getActualSalesChannel
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->setDataToLegacyStockStatus = $setDataToLegacyStockStatus;
        $this->isProductSalable = $isProductSalable;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->getActualSalesChannel = $getActualSalesChannel;
    }

    /**
     * @param SourceItemsSaveInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @see SourceItemsSaveInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(SourceItemsSaveInterface $subject, $result, array $sourceItems)
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
            $stockId = $this->defaultStockProvider->getId();
            $salesChannel = $this->getActualSalesChannel->execute($stockId);
            $this->setDataToLegacyStockStatus->execute(
                $sourceItem->getSku(),
                (float)$sourceItem->getQuantity(),
                (int)$this->isProductSalable->execute($sourceItem->getSku(), $stockId, $salesChannel)
            );
        }
    }
}
