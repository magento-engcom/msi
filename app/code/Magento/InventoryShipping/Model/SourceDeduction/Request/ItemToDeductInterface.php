<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceDeduction\Request;

/**
 * Represents requested quantity for particular product
 *
 * @api
 */
interface ItemToDeductInterface
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
}
