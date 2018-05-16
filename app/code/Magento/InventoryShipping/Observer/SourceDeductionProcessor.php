<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventoryShipping\Model\SourceDeduction\SourceDeductionServiceInterface;
use Magento\InventoryShipping\Model\SourceDeduction\Request\SourceDeductionRequestInterfaceFactory;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * Class SourceDeductionProcessor
 */
class SourceDeductionProcessor implements ObserverInterface
{
    /**
     * @var SourceDeductionRequestInterfaceFactory
     */
    private $sourceDeductionRequestFactory;

    /**
     * @var SourceDeductionServiceInterface
     */
    private $sourceDeductionService;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var GetItemsToDeduct
     */
    private $getItemsToDeduct;

    /**
     * @param SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory
     * @param SourceDeductionServiceInterface $sourceDeductionService
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param GetItemsToDeduct $getItemsToDeduct
     */
    public function __construct(
        SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory,
        SourceDeductionServiceInterface $sourceDeductionService,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SalesEventInterfaceFactory $salesEventFactory,
        IsSingleSourceModeInterface $isSingleSourceMode,
        GetItemsToDeduct $getItemsToDeduct
    ) {
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->salesEventFactory = $salesEventFactory;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->getItemsToDeduct = $getItemsToDeduct;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer): void
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        $shipmentItem = $observer->getShipmentItem();

        if ($shipmentItem->getOrigData('entity_id')) {
            return;
        }

        $shipment = $shipmentItem->getShipment();

        //TODO: I'm not sure that is good idea (with default source code)...
        if (!empty($shipment->getExtensionAttributes())
            || $shipment->getExtensionAttributes()->getSourceCode()) {
            $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
        } elseif ($this->isSingleSourceMode->execute()) {
            $sourceCode = $this->defaultSourceProvider->getCode();
        }

        $websiteId = $shipment->getOrder()->getStore()->getWebsiteId();

        $salesEvent = $this->salesEventFactory->create([
            'type' => SalesEventInterface::EVENT_SHIPMENT_CREATED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => $shipment->getOrderId()
        ]);

        if ($items = $this->getItemsToDeduct->execute($shipmentItem)) {
            $sourceDeductionRequest = $this->sourceDeductionRequestFactory->create([
                'websiteId' => $websiteId,
                'sourceCode' => $sourceCode,
                'items' => $items,
                'salesEvent' => $salesEvent
            ]);
            $this->sourceDeductionService->execute($sourceDeductionRequest);
        }
    }
}
