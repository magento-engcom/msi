<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * InlineEdit Controller
 */
class InlineEdit extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::stock';

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param StockRepositoryInterface $stockRepository
     */
    public function __construct(
        Context $context,
        DataObjectHelper $dataObjectHelper,
        StockRepositoryInterface $stockRepository
    ) {
        parent::__construct($context);
        $this->dataObjectHelper = $dataObjectHelper;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $errorMessages = [];
        $request = $this->getRequest();
        $requestData = $request->getParam('items', []);

        if ($request->isXmlHttpRequest() && $request->isPost() && $requestData) {
            foreach ($requestData as $itemData) {
                try {
                    $stock = $this->stockRepository->get(
                        $itemData[StockInterface::STOCK_ID]
                    );
                    $this->dataObjectHelper->populateWithArray($stock, $itemData, StockInterface::class);
                    $this->stockRepository->save($stock);
                } catch (NoSuchEntityException $e) {
                    $errorMessages[] = __(
                        '[ID: %value] The Stock does not exist.',
                        ['value' => $itemData[StockInterface::STOCK_ID]]
                    );
                } catch (CouldNotSaveException $e) {
                    $errorMessages[] = __('[ID: %value] %message', [
                        'value' => $itemData[StockInterface::STOCK_ID],
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } else {
            $errorMessages[] = __('Please correct the sent data.');
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData([
            'messages' => $errorMessages,
            'error' => count($errorMessages),
        ]);
        return $resultJson;
    }
}
