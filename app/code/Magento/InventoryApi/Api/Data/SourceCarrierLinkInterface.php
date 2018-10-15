<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents relation between some physical storage and shipping method
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceCarrierLinkInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const CARRIER_CODE = 'carrier_code';
    public const POSITION = 'position';
    public const SOURCE_CODE = 'source_code';
    /**#@-*/

    /**
     * Get carrier code
     *
     * @return string|null
     */
    public function getCarrierCode(): ?string;

    /**
     * Set carrier code
     *
     * @param string|null $carrierCode
     * @return void
     */
    public function setCarrierCode(?string $carrierCode): void;

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition(): ?int;

    /**
     * Set position
     *
     * @param int|null $position
     * @return void
     */
    public function setPosition(?int $position): void;

    /**
     * Retrieve existing extension attributes object
     *
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface|null
     */
    public function getExtensionAttributes(): ?SourceCarrierLinkExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\SourceCarrierLinkExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(SourceCarrierLinkExtensionInterface $extensionAttributes): void;
}
