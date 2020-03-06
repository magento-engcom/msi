<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for index price configurable.
 */
class IndexPriceTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $storeCodeBefore;

    /**
     * @var  ConfigurablePriceResolver
     */
    private $configurablePriceResolver;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
        $finalPrice = $this->objectManager->get(FinalPriceResolver::class);

        $this->configurablePriceResolver = $this->objectManager->create(
            ConfigurablePriceResolver::class,
            ['priceResolver' => $finalPrice]
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (null !== $this->storeCodeBefore) {
            $this->storeManager->setCurrentStore($this->storeCodeBefore);
        }
    }

    /**
     * Test index price when out of stock in default stock
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/set_product_configurable_out_of_stock_all.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDbIsolation disabled
     * @magentoConfigFixture store_for_us_website_store cataloginventory/options/show_out_of_stock 1
     * @return void
     */
    public function testIndexPriceWhenOutOfStockInDefaultStock(): void
    {
        $this->storeManager->setCurrentStore('store_for_us_website');
        $configurableProduct = $this->productRepository->get(
            'configurable',
            false,
            null,
            true
        );

        /** @var ResourceConnection $resource */
        $resource = $this->objectManager->get(ResourceConnection::class);
        /** @var Select $select */
        $select = $resource->getConnection()->select();
        $select->from(
            ['price_index' => $resource->getTableName('catalog_product_index_price')],
            ['entity_id', 'min_price', 'max_price']
        );
        $select->where("price_index.entity_id IN (?)", $configurableProduct->getId());
        $select->where('price_index.min_price = ?', 10);
        $select->where('price_index.max_price = ?', 20);
        $result = $select->query()->fetchAll();

        self::assertNotEquals(0, count($result));
    }
}
