<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Test\Api\StockRepository;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GetListTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stock';
    const SERVICE_NAME = 'inventoryApiStockRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock/stock_list.php
     * @param array $searchCriteria
     * @param array $expectedItemsData
     * @dataProvider dataProviderGetList
     */
    public function testGetList(array $searchCriteria, array $expectedItemsData)
    {
        $requestData = ['searchCriteria' => $searchCriteria];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);

        self::assertEquals(count($expectedItemsData), $response['total_count']);
        AssertArrayContains::assert($searchCriteria, $response['search_criteria']);
        AssertArrayContains::assert($expectedItemsData, $response['items']);
    }

    /**
     * @return array
     */
    public function dataProviderGetList()
    {
        return [
            'filtering_by_field' => [
                [
                    'filter_groups' => [
                        [
                            'filters' => [
                                [
                                    'field' => StockInterface::NAME,
                                    'value' => 'stock-name-2',
                                    'condition_type' => 'eq',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    [
                        StockInterface::NAME => 'stock-name-2',
                    ],
                ],
            ],
            'ordering_by_field' => [
                [
                    'filter_groups' => [], // It is need for soap mode
                    'sort_orders' => [
                        [
                            'field' => StockInterface::NAME,
                            'direction' => SortOrder::SORT_DESC,
                        ],
                    ],
                ],
                [
                    [
                        StockInterface::NAME => 'stock-name-4',
                    ],
                    [
                        StockInterface::NAME => 'stock-name-3',
                    ],
                    [
                        StockInterface::NAME => 'stock-name-2',
                    ],
                    [
                        StockInterface::NAME => 'stock-name-1',
                    ],
                ],
            ],
        ];
    }
}
