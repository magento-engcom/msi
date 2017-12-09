<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi\Api;

use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;
use Magento\InventoryCatalog\Model\Command\UpdateLegacyCatalogInventoryStockItemByPlainQueryInterface;
use Magento\InventoryCatalog\Model\Command\UpdateLegacyCatalogInventoryStockStatusByPlainQueryInterface;

/**
 * Plugin help to fill the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item to don't break the backward compatible.
 */
class UpdateLegacyCatalogInventoryAtStockDeductionTime
{
    /**
     * @var UpdateLegacyCatalogInventoryStockItemByPlainQueryInterface
     */
    private $updateLegacyStockItem;

    /**
     * @var UpdateLegacyCatalogInventoryStockStatusByPlainQueryInterface
     */
    private $updateLegacyStockStatus;

    /**
     * @param UpdateLegacyCatalogInventoryStockItemByPlainQueryInterface $updateLegacyStockItem
     * @param UpdateLegacyCatalogInventoryStockStatusByPlainQueryInterface $updateLegacyStockStatus
     */
    public function __construct(
        UpdateLegacyCatalogInventoryStockItemByPlainQueryInterface $updateLegacyStockItem,
        UpdateLegacyCatalogInventoryStockStatusByPlainQueryInterface $updateLegacyStockStatus
    ) {
        $this->updateLegacyStockItem = $updateLegacyStockItem;
        $this->updateLegacyStockStatus = $updateLegacyStockStatus;
    }

    /**
     * Plugin method to fill the legacy tables.
     *
     * @param ReservationsAppendInterface $subject
     * @param void $result
     * @param ReservationInterface[] $reservations
     *
     * @see ReservationsAppendInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function afterExecute(ReservationsAppendInterface $subject, $result, array $reservations)
    {
        $this->updateStockItemAndStatusTable($reservations);

        return $result;
    }

    /**
     * Updates cataloginventory_stock_item and cataloginventory_stock_status qty with reservation information.
     *
     * @param ReservationInterface[] $reservations
     *
     * @return void
     */
    private function updateStockItemAndStatusTable(array $reservations)
    {
        foreach ($reservations as $reservation) {
            $this->updateLegacyStockItem->execute($reservation);
            $this->updateLegacyStockStatus->execute($reservation);
        }
    }
}
