<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryLowStockNotificationApi\Api\DeleteSourceItemConfigurationInterface;

/** @var DeleteSourceItemConfigurationInterface $deleteSourceItemConfiguration */
$deleteSourceItemConfiguration = Bootstrap::getObjectManager()->get(DeleteSourceItemConfigurationInterface::class);
$deleteSourceItemConfiguration->execute('eu-1', 'SKU-1');
