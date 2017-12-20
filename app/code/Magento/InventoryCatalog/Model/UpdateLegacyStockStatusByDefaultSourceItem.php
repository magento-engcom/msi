<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Legacy update cataloginventory_stock_status by plain MySql query.
 * Use for skip save by \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::save
 */
class UpdateLegacyStockStatusByDefaultSourceItem
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param ProductIdLocatorInterface $productIdLocator
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider,
        DefaultStockProviderInterface $defaultStockProvider,
        ProductIdLocatorInterface $productIdLocator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->productIdLocator = $productIdLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(SourceItemInterface $sourceItem)
    {
        if ($sourceItem->getSourceId() != $this->defaultSourceProvider->getId()) {
            return;
        }

        $productIds = $this->productIdLocator->retrieveProductIdsBySkus([$sourceItem->getSku()]);
        $productId = isset($productIds[$sourceItem->getSku()]) ? key($productIds[$sourceItem->getSku()]) : false;

        if ($productId) {
            $connection = $this->resourceConnection->getConnection();
            $connection->update(
                $this->resourceConnection->getTableName('cataloginventory_stock_status'),
                [
                    StockStatusInterface::QTY => $sourceItem->getQuantity(),
                    StockStatusInterface::STOCK_STATUS => $sourceItem->getStatus()
                ],
                [
                    StockStatusInterface::STOCK_ID . ' = ?' => $this->defaultStockProvider->getId(),
                    StockStatusInterface::PRODUCT_ID . ' = ?' => $productId,
                    'website_id = ?' => 0
                ]
            );
        }
    }
}
