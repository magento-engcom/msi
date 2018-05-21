<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\SalesInventory;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventorySales\Model\ReturnProcessor\Request\ItemsToRefundInterfaceFactory;
use Magento\InventorySales\Model\ReturnProcessor\ProcessRefundItemsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessReturnQtyOnCreditMemoPlugin
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var ItemsToRefundInterfaceFactory
     */
    private $itemsToRefundFactory;

    /**
     * @var ProcessRefundItemsInterface
     */
    private $processRefundItems;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param ItemsToRefundInterfaceFactory $itemsToRefundFactory
     * @param ProcessRefundItemsInterface $processRefundItems
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        ItemsToRefundInterfaceFactory $itemsToRefundFactory,
        ProcessRefundItemsInterface $processRefundItems
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->itemsToRefundFactory = $itemsToRefundFactory;
        $this->processRefundItems = $processRefundItems;
    }

    /**
     * @param ReturnProcessor $subject
     * @param callable $proceed
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @param bool $isAutoReturn
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        ReturnProcessor $subject,
        callable $proceed,
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        array $returnToStockItems = [],
        bool $isAutoReturn = false
    ): void {
        $items = [];
        foreach ($creditmemo->getItems() as $item) {
            $orderItem = $item->getOrderItem();
            $qty = (float)$item->getQty();
            if ($this->canReturnItem($orderItem, $qty, $returnToStockItems) && !$orderItem->isDummy()) {
                $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                    [$item->getProductId()]
                )[$item->getProductId()];
                $processedQty = $orderItem->getQtyCanceled() - $orderItem->getQtyRefunded();
                $items[$itemSku] = [
                    'qty' => ($items[$itemSku]['qty'] ?? 0) + $qty,
                    'processedQty' => ($items[$itemSku]['processedQty'] ?? 0) + (float)$processedQty
                ];
            }
        }

        $itemsToRefund = [];
        foreach ($items as $sku => $data) {
            $itemsToRefund[] = $this->itemsToRefundFactory->create([
                'sku' => $sku,
                'qty' => $data['qty'],
                'processedQty' => $data['processedQty']
            ]);
        }
        $this->processRefundItems->execute($order, $itemsToRefund, $returnToStockItems);
    }

    /**
     * @param OrderItemInterface $orderItem
     * @param float $qty
     * @param array $returnToStockItems
     * @return bool
     */
    private function canReturnItem(OrderItemInterface $orderItem, float $qty, array $returnToStockItems): bool
    {
        $parentItemId = $orderItem->getParentItemId();
        return (in_array($orderItem->getId(), $returnToStockItems)
                || in_array($parentItemId, $returnToStockItems)) && $qty;
    }
}
