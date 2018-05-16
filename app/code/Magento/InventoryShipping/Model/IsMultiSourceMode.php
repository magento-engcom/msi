<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class IsMultiSourceMode
{
    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GetStockSourceLinksInterface
     */
    private $getStockSourceLinks;

    /**
     * isMultiSourceMode constructor.
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetStockSourceLinksInterface $getStockSourceLinks
     */
    public function __construct(
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetStockSourceLinksInterface $getStockSourceLinks
    ) {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getStockSourceLinks = $getStockSourceLinks;
    }

    /**
     * Check if is Multi Source Mode for website Id
     *
     * @param int $websiteId
     * @return bool
     */
    public function execute(int $websiteId): bool
    {
        $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->create();
        return $this->getStockSourceLinks->execute($searchCriteria)->getTotalCount() > 1;
    }
}
