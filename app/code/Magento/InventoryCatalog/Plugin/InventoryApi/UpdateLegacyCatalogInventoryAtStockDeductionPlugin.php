<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;
use Magento\InventoryCatalog\Model\UpdateLegacyStockItemByPlainQuery;
use Magento\InventoryCatalog\Model\UpdateLegacyStockStatusByPlainQuery;

/**
 * Plugin help to fill the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item to don't break the backward compatible.
 */
class UpdateLegacyCatalogInventoryAtStockDeductionPlugin
{
    /**
     * @var UpdateLegacyStockItemByPlainQuery
     */
    private $updateLegacyStockItem;

    /**
     * @var UpdateLegacyStockStatusByPlainQuery
     */
    private $updateLegacyStockStatus;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param UpdateLegacyStockItemByPlainQuery $updateLegacyStockItem
     * @param UpdateLegacyStockStatusByPlainQuery $updateLegacyStockStatus
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        UpdateLegacyStockItemByPlainQuery $updateLegacyStockItem,
        UpdateLegacyStockStatusByPlainQuery $updateLegacyStockStatus,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->updateLegacyStockItem = $updateLegacyStockItem;
        $this->updateLegacyStockStatus = $updateLegacyStockStatus;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Plugin method to fill the legacy tables.
     * Updates cataloginventory_stock_item and cataloginventory_stock_status qty with reservation information.
     *
     * @param ReservationsAppendInterface $subject
     * @param void $result
     * @param ReservationInterface[] $reservations
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(ReservationsAppendInterface $subject, $result, array $reservations)
    {
        if ($this->stockConfiguration->canSubtractQty()) {
            foreach ($reservations as $reservation) {
                $this->updateLegacyStockItem->execute($reservation->getSku(), (float)$reservation->getQuantity());
                $this->updateLegacyStockStatus->execute($reservation->getSku(), (float)$reservation->getQuantity());
            }
        }
    }
}