<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowStockNotification\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventoryLowStockNotification\Setup\Operation\CreateSourceConfigurationTable;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateSourceConfigurationTable
     */
    private $createSourceNotificationTable;

    /**
     * @param CreateSourceConfigurationTable $createSourceNotificationTable
     */
    public function __construct(
        CreateSourceConfigurationTable $createSourceNotificationTable
    ) {
        $this->createSourceNotificationTable = $createSourceNotificationTable;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->createSourceNotificationTable->execute($setup);
        $setup->endSetup();
    }
}
