<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

/**
 * Represents requested quantity for particular product
 *
 * @api
 */
interface ItemRequestInterface
{
    /**
     * Requested SKU
     *
     * @return string
     */
    public function getSku(): string;

    /**
     * Requested Product Quantity
     *
     * @return float
     */
    public function getQty(): float;

    /**
     * Set SKU
     *
     * @param $sku
     * @return void
     */
    public function setSku($sku);

    /**
     * Set Quantity
     *
     * @param $qty
     * @return void
     */
    public function setQty($qty);
}
