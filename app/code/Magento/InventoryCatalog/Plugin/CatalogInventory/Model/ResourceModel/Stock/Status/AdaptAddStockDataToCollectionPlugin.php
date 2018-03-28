<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\InventoryCatalog\Model\DefaultStockProvider;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection;

/**
 * Adapt adding stock data to collection for multi stocks.
 */
class AdaptAddStockDataToCollectionPlugin
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var AddStockDataToCollection
     */
    private $addStockDataToCollection;

    /**
     * @var DefaultStockProvider
     */
    private $defaultStockProvider;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AddStockDataToCollection $addStockDataToCollection
     * @param DefaultStockProvider $defaultStockProvider
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AddStockDataToCollection $addStockDataToCollection,
        DefaultStockProvider $defaultStockProvider
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->addStockDataToCollection = $addStockDataToCollection;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @param Status $stockStatus
     * @param callable $proceed
     * @param Collection $collection
     * @param bool $isFilterInStock
     * @return Collection $collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStockDataToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection,
        $isFilterInStock
    ) {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        if ($stockId === $this->defaultStockProvider->getId()) {
            $proceed($collection, $isFilterInStock);
        } else {
            $this->addStockDataToCollection->execute($collection, (bool)$isFilterInStock, $stockId);
        }

        return $collection;
    }
}
