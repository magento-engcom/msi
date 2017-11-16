<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;

/** @var SourceItemRepositoryInterface $sourceItemRepository */
$sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var SourceItemsDeleteInterface $deleteSourceItemsCommand */
$deleteSourceItemsCommand = Bootstrap::getObjectManager()->get(SourceItemsDeleteInterface::class);

$searchCriteria = $searchCriteriaBuilder
    ->addFilter(SourceItemInterface::SKU, ['SKU-1', 'SKU-2', 'SKU-3'], 'in')
    ->create();
$result = $sourceItemRepository->getList($searchCriteria);
$deleteSourceItemsCommand->execute($result->getItems());
