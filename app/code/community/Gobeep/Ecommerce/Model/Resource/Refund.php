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
class Gobeep_Ecommerce_Model_Resource_Refund extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Override to avoid updates
     */
    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('gobeep_ecommerce/refund', 'order_id');
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->join(
            ['o' => $this->getTable('sales/order')],
            $this->getMainTable() . '.order_id = o.entity_id',
            [
                'store_id' => 'o.store_id',
                'customer_email' => 'o.customer_email',
            ]
        );
        // Add customer information
        $select->join(
            ['oa' => $this->getTable('sales/order_address')],
            $this->getMainTable() . '.order_id = oa.parent_id',
            [
                'customer_firstname' => 'oa.firstname',
                'customer_lastname' => 'oa.lastname',
                'customer_city' => 'oa.city',
                'customer_country' => 'oa.country_id'
            ]
        );

        return $select;
    }
}
