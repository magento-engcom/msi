<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * InlineEdit Controller
 */
class InlineEdit extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        Context $context,
        DataObjectHelper $dataObjectHelper,
        SourceRepositoryInterface $sourceRepository
    ) {
        parent::__construct($context);
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceRepository = $sourceRepository;
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
                    $source = $this->sourceRepository->get(
                        $itemData[SourceInterface::SOURCE_ID]
                    );
                    $this->dataObjectHelper->populateWithArray($source, $itemData, SourceInterface::class);
                    $this->sourceRepository->save($source);
                } catch (NoSuchEntityException $e) {
                    $errorMessages[] = __(
                        '[ID: %value] The Source does not exist.',
                        ['value' => $itemData[SourceInterface::SOURCE_ID]]
                    );
                } catch (CouldNotSaveException $e) {
                    $errorMessages[] = __(
                        '[ID: %value] %message',
                        [
                            'value' => $itemData[SourceInterface::SOURCE_ID],
                            'message' => $e->getMessage(),
                        ]
                    );
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
