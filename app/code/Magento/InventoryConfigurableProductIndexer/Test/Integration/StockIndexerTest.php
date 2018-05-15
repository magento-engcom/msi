<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;
use Magento\InventoryIndexer\Model\ResourceModel\GetStockItemData;
use Magento\InventoryIndexer\Test\Integration\Indexer\RemoveIndexData;
use Magento\InventorySales\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockIndexerTest extends TestCase
{
    /**
     * @var StockIndexer
     */
    private $stockIndexer;

    /**
     * @var GetStockItemData
     */
    private $getStockItemData;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    /**
     * @var LinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemSave;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stockIndexer = Bootstrap::getObjectManager()->get(StockIndexer::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemData::class);
        $this->linkManagement = Bootstrap::getObjectManager()->get(LinkManagementInterface::class);
        $this->getSourceItemsBySku = Bootstrap::getObjectManager()->get(GetSourceItemsBySkuInterface::class);
        $this->sourceItemSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->isProductSalable = Bootstrap::getObjectManager()->get(IsProductSalableInterface::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testReindexList()
    {
        $configurableSku = 'configurable_1';

        $this->stockIndexer->executeList([10, 20, 30]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 10);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 20);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 30);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testReindexListSetAllSimplesOutOfStock()
    {
        $configurableSku = 'configurable_1';

        $children = $this->linkManagement->getChildren($configurableSku);
        foreach ($children as $child) {
            $sku = $child->getSku();
            $sourceItems = $this->getSourceItemsBySku->execute($sku);
            $changesSourceItems = [];
            foreach ($sourceItems->getItems() as $sourceItem) {
                $sourceItem->setStatus(SourceItemInterface::STATUS_OUT_OF_STOCK);
                $changesSourceItems[] = $sourceItem;
            }
            $this->sourceItemSave->execute($changesSourceItems);
        }

        $this->removeIndexData->execute([10, 20, 30]);
        $this->stockIndexer->executeList([10, 20, 30]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 10);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 20);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 30);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testReindexListSetAllEuSimplesOutOfStock()
    {
        $configurableSku = 'configurable_1';

        $sourceCodes = ['eu-1', 'eu-2', 'eu-3'];

        $children = $this->linkManagement->getChildren($configurableSku);
        foreach ($children as $child) {
            $sku = $child->getSku();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceItemInterface::SKU, $sku)
                ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCodes, 'in')
                ->create();
            $sourceItems = $this->sourceItemRepository->getList($searchCriteria);
            $changesSourceItems = [];
            foreach ($sourceItems->getItems() as $sourceItem) {
                $sourceItem->setStatus(SourceItemInterface::STATUS_OUT_OF_STOCK);
                $changesSourceItems[] = $sourceItem;
            }
            $this->sourceItemSave->execute($changesSourceItems);
        }

        $this->removeIndexData->execute([10, 20, 30]);
        $this->stockIndexer->executeList([10, 20, 30]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 10);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 20);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 30);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoAppArea frontend
     */
    // @codingStandardsIgnoreEnd
    public function testReindexListSetParentOutOfStock()
    {
        $configurableSku = 'configurable_1';
        $stockIds = [1, 10, 20, 30];
        $configurableProduct = $this->productRepository->get($configurableSku);
        $configurableProduct->setQuantityAndStockStatus(['is_in_stock' => 0]);
        $this->productRepository->save($configurableProduct);
        $this->stockIndexer->executeList($stockIds);

        foreach ($stockIds as $stockId) {
            self::assertEquals(
                false,
                $this->isProductSalable->execute($configurableSku, $stockId)
            );
        }
    }
}
