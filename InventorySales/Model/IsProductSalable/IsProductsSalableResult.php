<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalable;

use Magento\InventorySalesApi\Api\Data\IsProductSalableResultExtensionInterface;
use Magento\InventorySalesApi\Api\Data\IsProductSalableResultInterface;

/**
 * @inheritDoc
 */
class IsProductsSalableResult implements IsProductSalableResultInterface
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var bool
     */
    private $isSalable;

    /**
     * @var IsProductSalableResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param string $sku
     * @param bool $isSalable
     * @param IsProductSalableResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $sku,
        bool $isSalable,
        IsProductSalableResultExtensionInterface $extensionAttributes = null
    ) {
        $this->sku = $sku;
        $this->isSalable = $isSalable;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritDoc
     */
    public function isSalable(): bool
    {
        return $this->isSalable;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?IsProductSalableResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
