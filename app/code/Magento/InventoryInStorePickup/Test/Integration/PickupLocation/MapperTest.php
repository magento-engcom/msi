<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickup\Test\Integration\PickupLocation;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\InventoryApi\Api\Data\SourceExtensionInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryInStorePickupApi\Model\Mapper\CreateFromSourceInterface;
use Magento\InventoryInStorePickupApi\Model\Mapper;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationExtensionInterface;
use Magento\TestFramework\Helper\Bootstrap;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\InventoryApi\Api\SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var string
     */
    private $sourceCode;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->sourceRepository = $this->objectManager->create(SourceRepositoryInterface::class);
        $this->sourceCode = 'source-code-1';
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong mapping provided for Magento\InventoryApi\Api\Data\SourceInterface. Field 'source_fail_field' is not found.
     */
    public function testWrongMappingForSource()
    {
        $source = $this->sourceRepository->get($this->sourceCode);
        $map = $this->getMap();
        $map['source_fail_field'] = 'fail_field';
        /** @var  Mapper $mapper */
        $mapper = $this->objectManager->create(Mapper::class, ['map' => $map]);
        $mapper->map($source);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong mapping provided for Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface. Field 'extension_attributes.fail_field' is not found.
     */
    public function testWrongMappingForPickupLocationExtensionAttributes()
    {
        $source = $this->sourceRepository->get($this->sourceCode);
        $map = $this->getMap();
        $map['name'] = 'extension_attributes.fail_field';
        /** @var  Mapper $mapper */
        $mapper = $this->objectManager->create(Mapper::class, ['map' => $map]);
        $mapper->map($source);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong mapping provided for Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface. Field 'fail_field' is not found.
     */
    public function testWrongMappingForPickupLocation()
    {
        $source = $this->sourceRepository->get($this->sourceCode);
        $map = $this->getMap();
        $map['name'] = 'fail_field';
        /** @var  Mapper $mapper */
        $mapper = $this->objectManager->create(Mapper::class, ['map' => $map]);
        $mapper->map($source);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     */
    public function testMapPickupLocation()
    {
        $source = $this->sourceRepository->get($this->sourceCode);
        /** @var  Mapper $mapper */
        $mapper = $this->objectManager->create(Mapper::class, ['map' => $this->getMap()]);
        $pickupLocation = $mapper->map($source);

        $this->assertEquals($source->getSourceCode(), $pickupLocation->getSourceCode());
        $this->assertEquals($source->getEmail(), $pickupLocation->getEmail());
        $this->assertEquals($source->getContactName(), $pickupLocation->getContactName());
        $this->assertEquals($source->getDescription(), $pickupLocation->getDescription());
        $this->assertEquals($source->getLatitude(), $pickupLocation->getLatitude());
        $this->assertEquals($source->getLongitude(), $pickupLocation->getLongitude());
        $this->assertEquals($source->getCountryId(), $pickupLocation->getCountryId());
        $this->assertEquals($source->getRegionId(), $pickupLocation->getRegionId());
        $this->assertEquals($source->getRegion(), $pickupLocation->getRegion());
        $this->assertEquals($source->getCity(), $pickupLocation->getCity());
        $this->assertEquals($source->getStreet(), $pickupLocation->getStreet());
        $this->assertEquals($source->getPostcode(), $pickupLocation->getPostcode());
        $this->assertEquals($source->getPhone(), $pickupLocation->getPhone());
        $this->assertEquals($source->getFax(), $pickupLocation->getFax());
        $this->assertInstanceOf(PickupLocationExtensionInterface::class, $pickupLocation->getExtensionAttributes());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     */
    public function testMapPickupLocationWithExtensionAttributes()
    {
        $source = $this->sourceRepository->get($this->sourceCode);

        $sourceExtensionAttributes = $this->getMockBuilder(SourceExtensionInterface::class)
                                          ->disableOriginalConstructor()
                                          ->setMethods(['getOpenHours', 'getSomeAttribute'])
                                          ->getMockForAbstractClass();
        $sourceExtensionAttributes->expects($this->once())
                                  ->method('getOpenHours')
                                  ->willReturn(['open', 'hours']);
        $sourceExtensionAttributes->expects($this->once())
                                  ->method('getSomeAttribute')
                                  ->willReturn('some_value');
        $source->setExtensionAttributes($sourceExtensionAttributes);

        $pickupLocationExtension = $this->getMockBuilder(PickupLocationExtensionInterface::class)
                                        ->disableOriginalConstructor()
                                        ->setMethods(['setPickupLocationAttribute'])
                                        ->getMock();
        $pickupLocationExtension->expects($this->once())
                                ->method('setPickupLocationAttribute')
                                ->with('some_value');

        $extensionAttributesFactory = $this->getMockBuilder(ExtensionAttributesFactory::class)
                                           ->disableOriginalConstructor()
                                           ->getMock();
        $extensionAttributesFactory->expects($this->once())
                                   ->method('create')
                                   ->willReturn($pickupLocationExtension);

        $createFromSource = $this->objectManager->create(
            CreateFromSourceInterface::class,
            ['extensionAttributesFactory' => $extensionAttributesFactory]
        );

        $map = $this->getMap();
        $map['extension_attributes.open_hours'] = 'open_hours';
        $map['extension_attributes.some_attribute'] = 'extension_attributes.pickup_location_attribute';

        /** @var  Mapper $mapper */
        $mapper = $this->objectManager->create(
            Mapper::class,
            ['map' => $map, 'createFromSource' => $createFromSource]
        );
        $pickupLocation = $mapper->map($source);

        $this->assertEquals($source->getSourceCode(), $pickupLocation->getSourceCode());
        $this->assertEquals($source->getEmail(), $pickupLocation->getEmail());
        $this->assertEquals($source->getContactName(), $pickupLocation->getContactName());
        $this->assertEquals($source->getDescription(), $pickupLocation->getDescription());
        $this->assertEquals($source->getLatitude(), $pickupLocation->getLatitude());
        $this->assertEquals($source->getLongitude(), $pickupLocation->getLongitude());
        $this->assertEquals($source->getCountryId(), $pickupLocation->getCountryId());
        $this->assertEquals($source->getRegionId(), $pickupLocation->getRegionId());
        $this->assertEquals($source->getRegion(), $pickupLocation->getRegion());
        $this->assertEquals($source->getCity(), $pickupLocation->getCity());
        $this->assertEquals($source->getStreet(), $pickupLocation->getStreet());
        $this->assertEquals($source->getPostcode(), $pickupLocation->getPostcode());
        $this->assertEquals($source->getPhone(), $pickupLocation->getPhone());
        $this->assertEquals($source->getFax(), $pickupLocation->getFax());
        $this->assertEquals(['open', 'hours'], $pickupLocation->getOpenHours());
    }

    /**
     * @return array
     */
    private function getMap(): array
    {
        return [
            'source_code' => 'source_code',
            'email' => 'email',
            'fax' => 'fax',
            'contact_name' => 'contact_name',
            'description' => 'description',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'country_id' => 'country_id',
            'region_id' => 'region_id',
            'region' => 'region',
            'city' => 'city',
            'street' => 'street',
            'postcode' => 'postcode',
            'phone' => 'phone'
        ];
    }
}
