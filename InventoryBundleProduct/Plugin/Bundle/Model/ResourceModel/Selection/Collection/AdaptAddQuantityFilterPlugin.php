<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleProduct\Plugin\Bundle\Model\ResourceModel\Selection\Collection;

use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Adapt add quantity filter to bundle selection in multi stock environment plugin.
 */
class AdaptAddQuantityFilterPlugin
{
    /**
     * @var AreProductsSalableInterface
     */
    private $areProductsSalable;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @param AreProductsSalableInterface $areProductsSalable
     * @param StoreManagerInterface $storeManager
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     */
    public function __construct(
        AreProductsSalableInterface $areProductsSalable,
        StoreManagerInterface $storeManager,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
    ) {
        $this->areProductsSalable = $areProductsSalable;
        $this->storeManager = $storeManager;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
    }

    /**
     * Adapt quantity filter for multi stock environment.
     *
     * @param Collection $subject
     * @param \Closure $proceed
     * @return Collection
     */
    public function aroundAddQuantityFilter(
        Collection $subject,
        \Closure $proceed
    ): Collection {
        $website = $this->storeManager->getWebsite();
        $stock = $this->stockByWebsiteIdResolver->execute((int)$website->getId());
        $skus = [];
        $skusToExclude = [];
        foreach ($subject->getData() as $item) {
            $skus[] = (string)$item['sku'];
        }
        $results = $this->areProductsSalable->execute($skus, $stock->getStockId());
        foreach ($results as $result) {
            if (!$result->isSalable()) {
                $skusToExclude[] = $result->getSku();
            }
        }
        if ($skusToExclude) {
            $subject->getSelect()->where('e.sku NOT IN(?)', implode(',', $skusToExclude));
        }
        $subject->resetData();

        return $subject;
    }
}
