<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupGraphQl\Test\Api;

use Magento\Framework\App\ObjectManager;
use Magento\InventoryInStorePickupApi\Model\GetPickupLocationInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for Pickup Locations GraphQl endpoint.
 * Test negative test cases.
 */
class PickupLocationsNegativeCasesTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryInStorePickupApi/Test/_files/source_addresses.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryInStorePickupApi/Test/_files/source_pickup_location_attributes.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryInStorePickupApi/Test/_files/inventory_geoname.php
     *
     * @magentoConfigFixture default/cataloginventory/source_selection_distance_based/provider offline
     *
     * @magentoDbIsolation disabled
     *
     * @dataProvider dataProvider
     *
     * @param string $body
     * @param string $storeCode
     * @param string $expectedExceptionMessage
     *
     * @throws \Exception
     */
    public function testPickupLocationsEndpoint(
        string $body,
        string $storeCode,
        string $expectedExceptionMessage
    ) {
        $responseTemplate = <<<QUERY
{
    items {
      pickup_location_code
      name
      email
      fax
      description
      latitude
      longitude
      country_id
      region_id
      region
      city
      street
      postcode
      phone
    },
    total_count
    page_info {
      page_size
      current_page
      total_pages
    }
  }
QUERY;

        $request = '{' . PHP_EOL . $body . $responseTemplate . PHP_EOL . '}';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->graphQlQuery($request, [], '', ['Store' => $storeCode]);
    }

    /**
     * [
     *      GraphQl Request Body,
     *      Store Code,
     *      Exception Message,
     * ]
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProvider(): array
    {
        return [
           [/* Data set #0. Search by not existing address */
               'pickupLocations(
    distance:{
      radius: 750
      country_code: "ZZZ"
      postcode: "86559"
    }
    pageSize: 1
    currentPage: 1
    sort: {distance: ASC}
  )',
               'store_for_global_website',
               'Unknown geoname for  86559   ZZZ'
           ],
           [/* Data set #1. Wrong page size. */
            'pickupLocations(
    pageSize: -1
    currentPage: 1
  )',
            'store_for_global_website',
            'pageSize value must be greater than 0.'
           ],
           [/* Data set #2. Wrong current page. */
               'pickupLocations(
    pageSize: 10
    currentPage: -1
  )',
               'store_for_global_website',
               'currentPage value must be greater than 0.'
           ],
           [/* Data set #3. Wrong max page. */
               'pickupLocations(
    distance:{
      radius: 750
      country_code: "DE"
      postcode: "86559"
    }
    pageSize: 1
    currentPage: 4
    sort: {distance: ASC}
  )',
               'store_for_global_website',
               'currentPage value 4 specified is greater than the 2 page(s) available.'
           ],
           [/* Data set #4. Wrong distance filter input. */
               'pickupLocations(
    distance:{
      radius: 750
      country_code: "US"
    }
    pageSize: 1
    currentPage: 1
    sort: {distance: ASC}
  )',
               'store_for_global_website',
               'Region or city or postcode must be specified for the filter by distance.'
           ]
        ];
    }
}
