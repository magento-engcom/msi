<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\StockState;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class SuggestQtyPlugin
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockResolverInterface $stockResolver
     * @param StoreManagerInterface $storeManager
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockResolver = $stockResolver;
        $this->storeManager = $storeManager;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    /**
     * @param StockStateInterface $subject
     * @param \Closure $proceed
     * @param int $productId
     * @param float $qty
     * @param int|null $scopeId
     * @return float
     *
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSuggestQty(
        StockStateInterface $subject,
        \Closure $proceed,
        $productId,
        $qty,
        $scopeId
    ): float {
        try {
            $skus = $this->getSkusByProductIds->execute([$productId]);
            $productSku = $skus[$productId];

            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $stockId = (int)$stock->getStockId();

            $stockItemConfiguration = $this->getStockItemConfiguration->execute($productSku, $stockId);
            $qtyIncrements = $stockItemConfiguration->getQtyIncrements();

            if ($qty <= 0 || $stockItemConfiguration->isManageStock() === false || $qtyIncrements < 2) {
                throw new LocalizedException(__('Wrong condition.'));
            }

            $minQty = max($stockItemConfiguration->getMinSaleQty(), $qtyIncrements);
            $divisibleMin = ceil($minQty / $qtyIncrements) * $qtyIncrements;
            $maxQty = min(
                $this->getProductSalableQty->execute($productSku, $stockId) - $stockItemConfiguration->getMinQty(),
                $stockItemConfiguration->getMaxSaleQty()
            );
            $divisibleMax = floor($maxQty / $qtyIncrements) * $qtyIncrements;

            if ($qty < $minQty || $qty > $maxQty || $divisibleMin > $divisibleMax) {
                throw new LocalizedException(__('Wrong condition.'));
            }

            $closestDivisibleLeft = floor($qty / $qtyIncrements) * $qtyIncrements;
            $closestDivisibleRight = $closestDivisibleLeft + $qtyIncrements;
            $acceptableLeft = min(max($divisibleMin, $closestDivisibleLeft), $divisibleMax);
            $acceptableRight = max(min($divisibleMax, $closestDivisibleRight), $divisibleMin);

            return abs($acceptableLeft - $qty) < abs($acceptableRight - $qty) ? $acceptableLeft : $acceptableRight;
        } catch (LocalizedException $e) {
            return $qty;
        }
    }
}
