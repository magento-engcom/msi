<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;
use Magento\Inventory\Model\ResourceModel\SourceItem\GetSourceItemId;
use Magento\Inventory\Model\ResourceModel\SourceItem\SaveMultiple;
use Magento\Inventory\Model\SourceItem\Validator\SourceItemsValidator;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class SourceItemsSave implements SourceItemsSaveInterface
{
    /**
     * @var SourceItemsValidator
     */
    private $sourceItemsValidator;

    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SourceItemsReindex
     */
    private $sourceItemsReindex;

    /**
     * @var GetSourceItemId
     */
    private $getSourceItemId;

    /**
     * @param SourceItemsValidator $sourceItemsValidator
     * @param SaveMultiple $saveMultiple
     * @param SourceItemsReindex $sourceItemsReindex
     * @param GetSourceItemId $getSourceItemId
     * @param LoggerInterface $logger
     */
    public function __construct(
        SourceItemsValidator $sourceItemsValidator,
        SaveMultiple $saveMultiple,
        SourceItemsReindex $sourceItemsReindex,
        GetSourceItemId $getSourceItemId,
        LoggerInterface $logger
    ) {
        $this->sourceItemsValidator = $sourceItemsValidator;
        $this->saveMultiple = $saveMultiple;
        $this->logger = $logger;
        $this->sourceItemsReindex = $sourceItemsReindex;
        $this->getSourceItemId = $getSourceItemId;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceItems)
    {
        if (empty($sourceItems)) {
            throw new InputException(__('Input data is empty'));
        }

        $validationResult = $this->sourceItemsValidator->validate($sourceItems);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        try {
            $this->saveMultiple->execute($sourceItems);

            $sourceItemIds = array_map(
                function (SourceItemInterface $sourceItem) {
                    return $this->getSourceItemId->execute($sourceItem->getSku(), $sourceItem->getSourceCode());
                },
                $sourceItems
            );
            $this->sourceItemsReindex->execute($sourceItemIds);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source Item'), $e);
        }
    }
}
