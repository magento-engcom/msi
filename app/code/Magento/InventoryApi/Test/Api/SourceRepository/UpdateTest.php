<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Test\Api\SourceRepository;

use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class UpdateTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source/source.php
     */
    public function testUpdate()
    {
        $source = $this->getSourceDataByName('source-name-1');
        $sourceId = $source[SourceInterface::SOURCE_ID];
        $expectedData = [
            SourceInterface::NAME => 'source-name-1-updated',
            SourceInterface::CONTACT_NAME => 'source-contact-name-updated',
            SourceInterface::EMAIL => 'source-email-updated',
            SourceInterface::ENABLED => false,
            SourceInterface::DESCRIPTION => 'source-description-updated',
            SourceInterface::LATITUDE => 13.123456,
            SourceInterface::LONGITUDE => 14.123456,
            SourceInterface::COUNTRY_ID => 'UK',
            SourceInterface::REGION_ID => 12,
            SourceInterface::CITY => 'source-city-updated',
            SourceInterface::STREET => 'source-street-updated',
            SourceInterface::POSTCODE => 'source-postcode-updated',
            SourceInterface::PHONE => 'source-phone-updated',
            SourceInterface::FAX => 'source-fax-updated',
            SourceInterface::PRIORITY => 300,
            SourceInterface::USE_DEFAULT_CARRIER_CONFIG => 0,
            SourceInterface::CARRIER_LINKS => [
                [
                    SourceCarrierLinkInterface::CARRIER_CODE => 'ups-updated',
                    SourceCarrierLinkInterface::POSITION => 2000,
                ],
                [
                    SourceCarrierLinkInterface::CARRIER_CODE => 'usps-updated',
                    SourceCarrierLinkInterface::POSITION => 3000,
                ],
            ],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceId,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $this->_webApiCall($serviceInfo, ['source' => $expectedData]);
        } else {
            $requestData = $expectedData;
            $requestData['sourceId'] = $sourceId;
            $this->_webApiCall($serviceInfo, ['source' => $requestData]);
        }

        AssertArrayContains::assert($expectedData, $this->getSourceDataById($sourceId));
    }

    /**
     * @param string $name
     * @return array
     */
    private function getSourceDataByName($name)
    {
        $searchCriteria = [
            'filter_groups' => [
                [
                    'filters' => [
                        [
                            'field' => SourceInterface::NAME,
                            'value' => $name,
                            'condition_type' => 'eq',
                        ],
                    ],
                ],
                'page_size' => 1,
            ],
        ];
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

        self::assertArrayHasKey('items', $response);
        self::assertCount(1, $response['items']);
        return reset($response['items']);
    }

    /**
     * @param int $sourceId
     * @return array
     */
    private function getSourceDataById($sourceId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sourceId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['sourceId' => $sourceId]);
        self::assertArrayHasKey(SourceInterface::SOURCE_ID, $response);
        return $response;
    }
}
