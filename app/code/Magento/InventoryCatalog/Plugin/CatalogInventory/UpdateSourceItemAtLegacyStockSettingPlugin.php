<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\Catalog\Model\ProductSkuLocatorInterface;

/**
 * Class provides around Plugin on \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::save
 * to update data in Inventory source item based on legacy Stock Item data
 */
class UpdateSourceItemAtLegacyStockSettingPlugin
{
    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductSkuLocatorInterface
     */
    private $productSkuLocator;

    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param ResourceConnection $resourceConnection
     * @param ProductSkuLocatorInterface $productSkuLocator
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSave,
        DefaultSourceProviderInterface $defaultSourceProvider,
        ResourceConnection $resourceConnection,
        ProductSkuLocatorInterface $productSkuLocator
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->resourceConnection = $resourceConnection;
        $this->productSkuLocator = $productSkuLocator;
    }

    /**
     * @param ItemResourceModel $subject
     * @param callable $proceed
     * @param AbstractModel $legacyStockItem
     * @return ItemResourceModel
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(ItemResourceModel $subject, callable $proceed, AbstractModel $legacyStockItem)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            // need to save configuration
            $proceed($legacyStockItem);

            $this->updateSourceItemBasedOnLegacyStockItem($legacyStockItem);

            $connection->commit();
            return $subject;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param Item $legacyStockItem
     * @return void
     */
    private function updateSourceItemBasedOnLegacyStockItem(Item $legacyStockItem)
    {
        $productSku = $this->productSkuLocator
            ->retrieveSkusByProductIds([$legacyStockItem->getProductId()])[$legacyStockItem->getProductId()];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $productSku)
            ->addFilter(SourceItemInterface::SOURCE_ID, $this->defaultSourceProvider->getId())
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        if (count($sourceItems)) {
            $sourceItem = reset($sourceItems);
        } else {
            /** @var SourceItemInterface $sourceItem */
            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceId($this->defaultSourceProvider->getId());
            $sourceItem->setSku($productSku);
        }

        $sourceItem->setQuantity((float)$legacyStockItem->getQty());
        $sourceItem->setStatus((int)$legacyStockItem->getIsInStock());
        $this->sourceItemsSave->execute([$sourceItem]);
    }
}
