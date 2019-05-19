<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupApi\Model\Mapper;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;

/**
 * Create Pickup Location based on Source.
 * Transport data from Source to Pickup Location according to provided mapping.
 *
 * @api
 */
interface CreateFromSourceInterface
{
    /**
     * @param SourceInterface $source
     * @param array $map  May contains references to fields in extension attributes.
     * Please use format 'extension_attributes.field_name' to do so. E.g.
     * [
     *      "extension_attributes.source_field" => "pickup_location_field"
     *      "extension_attributes.source_field" => "extension_attributes.pickup_location_extension_field",
     * ]
     * @return PickupLocationInterface
     */
    public function execute(SourceInterface $source, array $map): PickupLocationInterface;
}
