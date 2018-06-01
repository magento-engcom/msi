<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalogAdminUi\Model\CanManageSourceItemsBySku;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;

/**
 * Product form modifier. Add to form source data
 */
class SourceItems extends AbstractModifier
{
    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var CanManageSourceItemsBySku
     */
    private $canManageSourceItemsBySku;

    /**
     * @var GetSourceItemsDataBySku
     */
    private $getSourceItemsDataBySku;

    /**
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     * @param CanManageSourceItemsBySku $canManageSourceItemsBySku
     * @param GetSourceItemsDataBySku $getSourceItemsDataBySku
     */
    public function __construct(
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        CanManageSourceItemsBySku $canManageSourceItemsBySku,
        GetSourceItemsDataBySku $getSourceItemsDataBySku
    ) {
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->canManageSourceItemsBySku = $canManageSourceItemsBySku;
        $this->getSourceItemsDataBySku = $getSourceItemsDataBySku;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        if ($this->isSingleSourceMode->execute() === true
            || $this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()) === false
            || null === $product->getId()
        ) {
            return $data;
        }

        $data[$product->getId()]['sources']['assigned_sources'] = $this->getSourceItemsDataBySku->execute(
            $product->getSku()
        );

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $product = $this->locator->getProduct();

        if ($this->isSingleSourceMode->execute() === true
            || $this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()) === false) {
            return $meta;
        }

        $canMangeSourceItems = $this->canManageSourceItemsBySku->execute($product->getSku());
        $meta['sources'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => 1,
                    ],
                ],
            ],
            'children' => [
                'assign_sources_container' => [
                    'children' => [
                        'assign_sources_button' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $canMangeSourceItems,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'assigned_sources' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => $canMangeSourceItems,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $meta;
    }
}
