<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Returns all assigned stock ids by given sources ids
 */
class GetPartialReindexData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SkuListInStockToUpdateFactory
     */
    private $skuListInStockToUpdateFactory;

    /**
     * @var int
     */
    private $groupConcatMaxLen;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SkuListInStockToUpdateFactory $skuListInStockToUpdateFactory
     * @param int $groupConcatMaxLen
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SkuListInStockToUpdateFactory $skuListInStockToUpdateFactory,
        int $groupConcatMaxLen
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->skuListInStockToUpdateFactory = $skuListInStockToUpdateFactory;
        $this->groupConcatMaxLen = $groupConcatMaxLen;
    }

    /**
     * Returns all assigned stock ids by given sources item ids.
     *
     * @param int[] $sourceItemIds
     * @return SkuListInStockToUpdate[] List of stock id to sku1,sku2 assignment
     */
    public function execute(array $sourceItemIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceStockLinkTable = $this->resourceConnection->getTableName(
            StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK
        );
        $sourceItemTable = $this->resourceConnection->getTableName(
            SourceItem::TABLE_NAME_SOURCE_ITEM
        );

        $select = $connection
            ->select()
            ->from(
                ['source_item' => $sourceItemTable],
                [
                    SourceItemInterface::SKU =>
                        sprintf("GROUP_CONCAT(DISTINCT %s SEPARATOR ',')", 'source_item.' . SourceItemInterface::SKU)
                ]
            )->joinInner(
                ['stock_source_link' => $sourceStockLinkTable],
                'source_item.' . SourceItemInterface::SOURCE_ID . ' = stock_source_link.' . StockSourceLink::SOURCE_ID,
                [StockSourceLink::STOCK_ID]
            )->where('source_item.source_item_id IN (?)', $sourceItemIds)
            ->group(['stock_source_link.' . StockSourceLink::STOCK_ID]);

        $connection->query('SET group_concat_max_len = ' . $this->groupConcatMaxLen);
        $items = $connection->fetchAll($select);
        return $this->getStockIdToSkuList($items);
    }

    /**
     * Return the assigned stock id to sku list.
     * @param array $items
     * @return SkuListInStockToUpdate[]
     */
    private function getStockIdToSkuList(array $items): array
    {
        $skuListInStockToUpdateList = [];
        foreach ($items as $item) {
            /** @var  SkuListInStockToUpdate $skuListInStockToUpdate */
            $skuListInStockToUpdate = $this->skuListInStockToUpdateFactory->create();
            $skuListInStockToUpdate->setStockId($item[StockSourceLink::STOCK_ID]);
            $skuListInStockToUpdate->setSkuList(explode(',', $item[SourceItemInterface::SKU]));
            $skuListInStockToUpdateList[] = $skuListInStockToUpdate;
        }

        return $skuListInStockToUpdateList;
    }
}
