<?php

/**
 * GoBeep
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    GoBeep
 * @package     Gobeep_Ecommerce
 * @author      Christophe Eblé <ceble@gobeep.co>
 * @copyright   Copyright (c) GoBeep (https://gobeep.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('gobeep_ecommerce/refund'))
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
    ), 'Order ID')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(), 'Order Increment ID')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array('nullable' => false, 'default' => Gobeep_Ecommerce_Model_Refund::STATUS_PENDING))
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => true, 'default' => null))
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => true, 'default' => null))
    ->addColumn('email_sent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array('unsigned'  => true), 'Email Sent')
    ->addIndex($installer->getIdxName('gobeep_ecommerce/refund', 'order_id'), 'order_id')
    ->addForeignKey(
        $installer->getFkName('gobeep_ecommerce/refund', 'order_id', 'sales/order', 'entity_id'),
        'order_id',
        $installer->getTable('sales/order'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Gobeep Refunds Table');

$installer->getConnection()->createTable($table);

$installer->endSetup();
