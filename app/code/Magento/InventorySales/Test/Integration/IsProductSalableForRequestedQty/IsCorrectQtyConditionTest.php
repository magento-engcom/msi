<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\IsProductSalableForRequestedQty;

use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\SaveStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsCorrectQtyConditionTest extends TestCase
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfig;

    /**
     * @var SaveStockItemConfigurationInterface
     */
    private $saveStockItemConfig;

    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->getStockItemConfig = Bootstrap::getObjectManager()->get(GetStockItemConfigurationInterface::class);
        $this->saveStockItemConfig = Bootstrap::getObjectManager()->get(SaveStockItemConfigurationInterface::class);
        $this->isProductSalableForRequestedQty
            = Bootstrap::getObjectManager()->get(IsProductSalableForRequestedQtyInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider executeWithMissingConfigurationDataProvider
     */
    public function testExecuteWithMissingConfiguration($sku, $stockId, $requestedQty, bool $expectedResult)
    {
        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    public function executeWithMissingConfigurationDataProvider(): array
    {
        return [
            ['SKU-2', 10, 1, false],
        ];
    }

    public function testExecuteWithUseConfigMinSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    public function testExecuteWithMinSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    public function testExecuteWithUseConfigMaxSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    public function testExecuteWithMaxSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    /**
     * Test IsProductSalableForRequestedQty takes into consideration "decimal qty" value for product.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider qtyIncrementsDataProvider
     * @param string $sku
     * @param int $stockId
     * @param float $qty
     * @param bool $expected
     */
    public function testExecuteWithQtyIncrements(string $sku, int $stockId, float $qty, bool $expected)
    {
        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $qty);
        $this->assertEquals($expected, $result->isSalable());
    }

    /**
     * Provide test data for testExecuteWithQtyIncrements.
     *
     * @return array
     */
    public function qtyIncrementsDataProvider(): array
    {
        return [
            [
                'product_sku' => 'SKU-1',
                'stock_id' => 10,
                'qty' => 1.5,
                'is_salable' => true,
            ],
            [
                'product_sku' => 'SKU-2',
                'stock_id' => 20,
                'qty' => 2,
                'is_salable' => true,
            ],
            [
                'product_sku' => 'SKU-2',
                'stock_id' => 20,
                'qty' => 1.5,
                'is_salable' => false,
            ],
        ];
    }
}
