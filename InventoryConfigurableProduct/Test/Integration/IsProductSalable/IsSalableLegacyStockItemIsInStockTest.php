<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\IsProductSalable;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkus;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify legacy stock item stock status.
 */
class IsSalableLegacyStockItemIsInStockTest extends TestCase
{
    /**
     * @var AreProductsSalableInterface
     */
    private $areProductsSalable;

    /**
     * @var GetProductIdsBySkus
     */
    private $getProductIdsBySkus;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->areProductsSalable = Bootstrap::getObjectManager()->get(AreProductsSalableInterface::class);
        $this->getProductIdsBySkus = Bootstrap::getObjectManager()->get(GetProductIdsBySkus::class);
        $this->stockRegistryProvider = Bootstrap::getObjectManager()->get(StockRegistryProviderInterface::class);
        $this->stockConfiguration = Bootstrap::getObjectManager()->get(StockConfigurationInterface::class);
        $this->stockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
    }

    /**
     * Verify 'out of stock' legacy stock item.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDbIsolation disabled
     */
    public function testIsProductSalableLegacyStockItemIsOutOfStock(): void
    {
        $sku = 'configurable';
        $stockId = 20;
        $this->setLegacyStockItemIsInStock($sku, 0);

        $result = $this->areProductsSalable->execute($sku, $stockId)->getSalable();
        $result = current($result);
        self::assertEquals(
            false,
            $result->isSalable()
        );
    }

    /**
     * Verify 'in stock' legacy stock item.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDbIsolation disabled
     */
    public function testIsProductSalableLegacyStockItemIsInStock(): void
    {
        $sku = 'configurable';
        $stockId = 20;
        $this->setLegacyStockItemIsInStock($sku, 1);

        $result = $this->areProductsSalable->execute($sku, $stockId)->getSalable();
        $result = current($result);
        self::assertEquals(
            true,
            $result->isSalable()
        );
    }

    /**
     * Set stock status to legacy stock item.
     *
     * @param string $sku
     * @param int $isInStock
     * @return void
     */
    private function setLegacyStockItemIsInStock(string $sku, int $isInStock): void
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $productId = current($this->getProductIdsBySkus->execute([$sku]));
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $stockItem->setIsInStock($isInStock);
        $this->stockItemRepository->save($stockItem);
    }
}
