<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SourceItemRepositoryInterface $sourceItemRepository */
$sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var SourceItemsDeleteInterface $sourceItemDelete */
$sourceItemDelete = Bootstrap::getObjectManager()->get(SourceItemsDeleteInterface::class);

$searchCriteria = $searchCriteriaBuilder
    ->addFilter(SourceItemInterface::SKU, ['SKU-1', 'SKU-2', 'SKU-3'], 'in')
    ->create();
$sourceItems = $sourceItemRepository->getList($searchCriteria)->getItems();

if ($sourceItems) {
    $sourceItemDelete->execute($sourceItems);
}
