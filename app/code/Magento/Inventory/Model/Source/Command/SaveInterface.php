<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Save Source data command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial Save call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * @see \Magento\InventoryApi\Api\SourceRepositoryInterface
 * @api
 */
interface SaveInterface
{
    /**
     * Save Source data
     *
     * @param SourceInterface $source
     * @return int
     * @throws ValidationException
     * @throws CouldNotSaveException
     */
    public function execute(SourceInterface $source);
}
