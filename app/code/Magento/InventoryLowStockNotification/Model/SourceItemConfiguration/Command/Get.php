<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowStockNotification\Model\SourceItemConfiguration\Command;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryLowStockNotification\Model\ResourceModel\SourceItemConfiguration\GetData as GetDataResourceModel;
use Magento\InventoryLowStockNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowStockNotificationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryLowStockNotificationApi\Api\GetSourceItemConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Get implements GetSourceItemConfigurationInterface
{
    /**
     * @var GetDataResourceModel
     */
    private $getDataResourceModel;

    /**
     * @var GetDefaultValues
     */
    private $getDefaultValues;

    /**
     * @var SourceItemConfigurationInterfaceFactory
     */
    private $sourceItemConfigurationFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GetDataResourceModel $getDataResourceModel
     * @param GetDefaultValues $getDefaultValues
     * @param SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetDataResourceModel $getDataResourceModel,
        GetDefaultValues $getDefaultValues,
        SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory,
        DataObjectHelper $dataObjectHelper,
        LoggerInterface $logger
    ) {
        $this->getDataResourceModel = $getDataResourceModel;
        $this->getDefaultValues = $getDefaultValues;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sourceCode, string $sku): SourceItemConfigurationInterface
    {
        if (empty($sourceCode) || empty($sku)) {
            throw new InputException(__('Wrong input data'));
        }

        try {
            return $this->getConfiguration($sourceCode, $sku);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Could not load Source Item Configuration.'), $e);
        }
    }

    /**
     * @param string $sourceCode
     * @param string $sku
     * @return SourceItemConfigurationInterface
     */
    private function getConfiguration(string $sourceCode, string $sku): SourceItemConfigurationInterface
    {
        $sourceItemConfigurationData = $this->getDataResourceModel->execute($sourceCode, $sku);

        if (null === $sourceItemConfigurationData) {
            $sourceItemConfigurationData = $this->getDefaultValues->execute($sourceCode, $sku);
        }

        /** @var SourceItemConfigurationInterface $sourceItem */
        $sourceItemConfiguration = $this->sourceItemConfigurationFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $sourceItemConfiguration,
            $sourceItemConfigurationData,
            SourceItemConfigurationInterface::class
        );
        return $sourceItemConfiguration;
    }
}
