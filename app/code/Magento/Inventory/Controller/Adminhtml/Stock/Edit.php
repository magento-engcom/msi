<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Edit Controller
 */
class Edit extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::stock';

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @param Context $context
     * @param StockRepositoryInterface $stockRepository
     */
    public function __construct(
        Context $context,
        StockRepositoryInterface $stockRepository
    ) {
        parent::__construct($context);
        $this->stockRepository = $stockRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $stockId = $this->getRequest()->getParam(StockInterface::STOCK_ID);
        try {
            $stock = $this->stockRepository->get($stockId);

            /** @var Page $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $result->setActiveMenu('Magento_Inventory::stock')
                ->addBreadcrumb(__('Edit Stock'), __('Edit Stock'));
            $result->getConfig()
                ->getTitle()
                ->prepend(__('Edit Stock: %name', ['name' => $stock->getName()]));
        } catch (NoSuchEntityException $e) {
            /** @var Redirect $result */
            $result = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(
                __('Stock with id "%value" does not exist.', ['value' => $stockId])
            );
            $result->setPath('*/*');
        }
        return $result;
    }
}
