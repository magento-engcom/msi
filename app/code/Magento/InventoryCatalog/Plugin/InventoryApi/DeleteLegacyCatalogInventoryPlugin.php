<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Model\DeleteLegacyStockItemByDefaultSourceItem;
use Magento\InventoryCatalog\Model\DeleteLegacyStockStatusByDefaultSourceItem;

/**
 * Plugin help to delete related entries from the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item if deleted source item is default source item.
 */
class DeleteLegacyCatalogInventoryPlugin
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var DeleteLegacyStockItemByDefaultSourceItem
     */
    private $deleteStockItemBySourceItem;

    /**
     * @var DeleteLegacyStockStatusByDefaultSourceItem
     */
    private $deleteStockStatusBySourceItem;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param DeleteLegacyStockItemByDefaultSourceItem $deleteStockItemBySourceItem
     * @param DeleteLegacyStockStatusByDefaultSourceItem $deleteStockStatusBySourceItem
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        DeleteLegacyStockItemByDefaultSourceItem $deleteStockItemBySourceItem,
        DeleteLegacyStockStatusByDefaultSourceItem $deleteStockStatusBySourceItem
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->deleteStockItemBySourceItem = $deleteStockItemBySourceItem;
        $this->deleteStockStatusBySourceItem = $deleteStockStatusBySourceItem;
    }

    /**
     * Plugin method to delete entry from the legacy tables.
     *
     * @param SourceItemsDeleteInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     *
     * @see SourceItemsDeleteInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(SourceItemsDeleteInterface $subject, $result, array $sourceItems)
    {
        $defaultSourceId = $this->defaultSourceProvider->getId();

        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getSourceId() == $defaultSourceId) {
                $this->deleteStockItemBySourceItem->execute($sourceItem);
                $this->deleteStockStatusBySourceItem->execute($sourceItem);
            }
        }
    }
}