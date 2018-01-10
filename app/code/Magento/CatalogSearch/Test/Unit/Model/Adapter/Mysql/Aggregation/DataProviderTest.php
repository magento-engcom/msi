<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Aggregation;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider;
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\StockSelectProviderInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Ddl\Table;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataProvider
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var StockSelectProviderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $selectProvider;

    protected function setUp()
    {
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->scopeResolverMock = $this->createMock(ScopeResolverInterface::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->selectProvider  = $this->createMock(StockSelectProviderInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($this->adapterMock);

        $this->model = new DataProvider(
            $this->eavConfigMock,
            $this->resourceConnectionMock,
            $this->scopeResolverMock,
            $this->sessionMock,
            $this->selectProvider
        );
    }

    public function testGetDataSetUsesFrontendPriceIndexerTableIfAttributeIsPrice()
    {
        $storeId = 1;
        $attributeCode = 'price';

        $scopeMock = $this->createMock(Store::class);
        $scopeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $dimensionMock = $this->createMock(Dimension::class);
        $dimensionMock->expects($this->any())->method('getValue')->willReturn($storeId);
        $this->scopeResolverMock->expects($this->any())->method('getScope')->with($storeId)->willReturn($scopeMock);

        $bucketMock = $this->createMock(BucketInterface::class);
        $bucketMock->expects($this->once())->method('getField')->willReturn($attributeCode);
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')->with(Product::ENTITY, $attributeCode)
            ->willReturn($attributeMock);

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('columns')->willReturnSelf();
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $tableMock = $this->createMock(Table::class);

        $this->model->getDataSet($bucketMock, ['scope' => $dimensionMock], $tableMock);
    }

    public function testGetDataSetUsesFrontendPriceIndexerTableForDecimalAttributes()
    {
        $storeId = 1;
        $attributeCode = 'my_decimal';

        $scopeMock = $this->createMock(Store::class);
        $scopeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $dimensionMock = $this->createMock(Dimension::class);
        $dimensionMock->expects($this->any())->method('getValue')->willReturn($storeId);
        $this->scopeResolverMock->expects($this->any())->method('getScope')->with($storeId)->willReturn($scopeMock);

        $bucketMock = $this->createMock(BucketInterface::class);
        $bucketMock->expects($this->once())->method('getField')->willReturn($attributeCode);
        $attributeMock = $this->createMock(Attribute::class);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')->with(Product::ENTITY, $attributeCode)
            ->willReturn($attributeMock);

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('distinct')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('columns')->willReturnSelf();
        $selectMock->expects($this->any())->method('joinLeft')->willReturnSelf();
        $selectMock->expects($this->any())->method('group')->willReturnSelf();
        $this->adapterMock->expects($this->any())->method('select')->willReturn($selectMock);
        $tableMock = $this->createMock(Table::class);
        $this->model->getDataSet($bucketMock, ['scope' => $dimensionMock], $tableMock);
    }
}
