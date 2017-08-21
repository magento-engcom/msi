<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Inventory\Model\ResourceModel\Reservation as ReservationResourceModel;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

class CreateReservationTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $reservationTable = $this->createReservationTable($setup);

        $setup->getConnection()->createTable($reservationTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createReservationTable(SchemaSetupInterface $setup)
    {
        $reservationTable = $setup->getTable(ReservationResourceModel::TABLE_NAME_RESERVATION);
        $stockTable = $setup->getTable(StockResourceModel::TABLE_NAME_STOCK);

        return $setup->getConnection()->newTable(
            $reservationTable
        )->setComment(
            'Inventory Reservation Table'
        )->addColumn(
            ReservationInterface::RESERVATION_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Reservation ID'
        )->addColumn(
            ReservationInterface::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_UNSIGNED => true,
            ],
            'Stock ID'
        )->addColumn(
            ReservationInterface::SKU,
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Sku'
        )->addColumn(
            ReservationInterface::QUANTITY,
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
            ],
            'Quantity'
        )->addColumn(
            ReservationInterface::METADATA,
            Table::TYPE_TEXT,
            '64k',
            [],
            'Metadata'
        )->addForeignKey(
            $setup->getFkName(
                $reservationTable,
                ReservationInterface::STOCK_ID,
                $stockTable,
                StockInterface::STOCK_ID
            ),
            ReservationInterface::STOCK_ID,
            $stockTable,
            StockInterface::STOCK_ID,
            AdapterInterface::FK_ACTION_CASCADE
        )->addIndex(
            $setup->getIdxName(
                $reservationTable,
                [
                    ReservationInterface::STOCK_ID,
                    ReservationInterface::SKU,
                    ReservationInterface::QUANTITY,
                ],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [
                ReservationInterface::STOCK_ID,
                ReservationInterface::SKU,
                ReservationInterface::QUANTITY,
            ],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        );
    }
}
