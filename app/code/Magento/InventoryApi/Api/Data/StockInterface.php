<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents product aggregation among some different physical storages (in technical words, it is an index)
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface StockInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const STOCK_ID = 'stock_id';
    public const NAME = 'name';
    /**#@-*/

    /**
     * Get stock id
     *
     * @return int|null
     */
    public function getStockId(): ?int;

    /**
     * Set stock id
     *
     * @param int|null $stockId
     * @return void
     */
    public function setStockId(?int $stockId): void;

    /**
     * Get stock name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set stock name
     *
     * @param string|null $name
     * @return void
     */
    public function setName(?string $name): void;

    /**
     * Retrieve existing extension attributes object
     *
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventoryApi\Api\Data\StockExtensionInterface|null
     */
    public function getExtensionAttributes(): ?StockExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\StockExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(StockExtensionInterface $extensionAttributes): void;
}
