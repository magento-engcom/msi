<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Shipping\Model\Config;

/**
 * Prepare carriers data. Specified for form structure
 */
class SourceCarrierDataProcessor
{
    /**
     * Shipping config
     *
     * @var Config
     */
    private $shippingConfig;

    /**
     * @param Config $shippingConfig
     */
    public function __construct(
        Config $shippingConfig
    ) {
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @param array $data
     * @return array
     * @throws InputException
     */
    public function process(array $data)
    {
        $useDefaultCarrierConfig = isset($data[SourceInterface::USE_DEFAULT_CARRIER_CONFIG])
            && true === (bool)$data[SourceInterface::USE_DEFAULT_CARRIER_CONFIG];

        if (false === $useDefaultCarrierConfig
            && isset($data['carrier_codes'])
            && is_array($data['carrier_codes'])
        ) {
            $this->checkCarrierCodes($data['carrier_codes']);
            $data[SourceInterface::CARRIER_LINKS] = $this->getCarrierLinksData($data['carrier_codes']);
        } else {
            $data[SourceInterface::CARRIER_LINKS] = [];
        }
        unset($data['carrier_codes']);
        return $data;
    }

    /**
     * @param array $carrierCodes
     * @return void
     * @throws InputException
     */
    private function checkCarrierCodes(array $carrierCodes)
    {
        $availableCarriers = $this->shippingConfig->getAllCarriers();

        // TODO: move validation to service (management + tests)
        if (count(array_intersect_key(array_flip($carrierCodes), $availableCarriers)) !== count($carrierCodes)) {
            throw new InputException(__('Wrong carrier codes data'));
        }
    }

    /**
     * @param array $carrierCodes
     * @return array
     */
    private function getCarrierLinksData(array $carrierCodes)
    {
        $carrierLinks = [];
        foreach ($carrierCodes as $carrierCode) {
            $carrierLinks[] = [
                SourceCarrierLinkInterface::CARRIER_CODE => $carrierCode,
            ];
        }
        return $carrierLinks;
    }
}
