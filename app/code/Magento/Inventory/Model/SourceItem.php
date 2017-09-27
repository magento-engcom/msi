<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemExtensionInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class SourceItem extends AbstractExtensibleModel implements SourceItemInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SourceItemResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->getData(self::SKU);


    }

    // broken Code Style


    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getSourceId()
    {
        return $this->getData(self::SOURCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSourceId($sourceId)
    {
        $this->setData(self::SOURCE_ID, $sourceId);
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceItemInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceItemExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
