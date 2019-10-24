<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupApi\Api\Data\SearchRequest;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Filter by Distance to the Address.
 * Pickup Locations will be filtered by distance according to the geo-position of the entered address.
 * Required fields for the address are country and one of the field: region or city or postcode.
 *
 * @api
 */
interface DistanceFilterInterface extends ExtensibleDataInterface
{
    public const DISTANCE_FIELD = 'distance';

    /**
     * Get search radius in KM.
     *
     * @return int
     */
    public function getRadius(): int;

    /**
     * Requested country.
     *
     * @return string
     */
    public function getCountry(): string;

    /**
     * Requested region.
     *
     * @return string|null
     */
    public function getRegion(): ?string;

    /**
     * Requested city.
     *
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * Requested postcode.
     *
     * @return string|null
     */
    public function getPostcode(): ?string;

    /**
     * Set Extension Attributes for Distance Filter.
     *
     * phpcs:disable Magento2.Files.LineLength.MaxExceeded
     *
     * @param \Magento\InventoryInStorePickupApi\Api\Data\SearchRequest\DistanceFilterExtensionInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(?DistanceFilterExtensionInterface $extensionAttributes): void;

    /**
     * Get Extension Attributes for Distance Filter.
     *
     * @return \Magento\InventoryInStorePickupApi\Api\Data\SearchRequest\DistanceFilterExtensionInterface|null
     */
    public function getExtensionAttributes(): ?DistanceFilterExtensionInterface;
}
