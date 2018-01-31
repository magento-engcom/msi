<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowStockNotification\Model\SourceItemConfiguration\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\InventoryLowStockNotification\Model\ResourceModel\SourceItemConfiguration\SaveMultiple
    as SaveMultipleResourceModel;
use Magento\InventoryLowStockNotificationApi\Api\SourceItemConfigurationsSaveInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class SaveMultiple implements SourceItemConfigurationsSaveInterface
{
    /**
     * @var SaveMultipleResourceModel
     */
    private $saveMultipleResourceModel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SaveMultipleResourceModel $saveMultipleResourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveMultipleResourceModel $saveMultipleResourceModel,
        LoggerInterface $logger
    ) {
        $this->saveMultipleResourceModel = $saveMultipleResourceModel;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceItemConfigurations)
    {
        if (empty($sourceItemConfigurations)) {
            throw new InputException(__('Input data is empty'));
        }
        try {
            $this->saveMultipleResourceModel->execute($sourceItemConfigurations);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source Item Configuration'), $e);
        }
    }
}
