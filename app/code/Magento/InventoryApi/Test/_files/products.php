<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;

const PRODUCT_COUNT = 4;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$stockData = [
    'SKU-1' => [
        'qty' => 8.5,
        'is_in_stock' => true,
        'manage_stock' => true
    ],
    'SKU-2' => [
        'qty' => 5,
        'is_in_stock' => true,
        'manage_stock' => true
    ],
    'SKU-3' => [
        'qty' => 0,
        'is_in_stock' => false,
        'manage_stock' => true
    ],
    'SKU-4' => [
        'qty' => 10,
        'is_in_stock' => true,
        'manage_stock' => true
    ]
];

for ($i = 1; $i <= PRODUCT_COUNT; $i++) {
    $product = $productFactory->create();
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setName('Simple Product ' . $i)
        ->setSku('SKU-' . $i)
        ->setPrice(10)
        ->setStockData($stockData['SKU-' . $i])
        ->setStatus(Status::STATUS_ENABLED);
    $productRepository->save($product);
}
