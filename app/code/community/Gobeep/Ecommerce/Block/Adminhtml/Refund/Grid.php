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
class Gobeep_Ecommerce_Block_Adminhtml_Refund_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('gobeep_ecommerce_refund_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass()
    {
        return 'gobeep_ecommerce/refund_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        // Add customer information
        $collection->getSelect()->join(
            ['oa' => $collection->getTable('sales/order_address')],
            'main_table.order_id = oa.parent_id',
            [
                'customer_firstname' => 'oa.firstname',
                'customer_lastname' => 'oa.lastname',
                'customer_city' => 'oa.city',
                'customer_country' => 'oa.country_id'
            ]
        );

        // Add order information
        $collection->getSelect()->join(
            ['o' => $collection->getTable('sales/order')],
            'main_table.order_id = o.entity_id',
            [
                'store_id' => 'o.store_id',
                'base_grand_total' => 'o.base_grand_total',
                'grand_total' => 'o.grand_total',
                'base_currency_code' => 'o.base_currency_code',
                'order_currency_code' => 'o.order_currency_code',
                'order_status' => 'o.status'
            ]
        );

        $collection->getSelect()->where('oa.address_type = \'billing\'');
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('order_id', [
            'header' => Mage::helper('sales')->__('Order #'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'order_increment_id'
        ]);

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', [
                'header'          => $this->__('Purchased From (Store)'),
                'index'           => 'store_id',
                'type'            => 'store',
                'store_view'      => true,
                'display_deleted' => true,
            ]);
        }
        $this->addColumn('base_grand_total', [
            'header' => $this->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ]);
        $this->addColumn('grand_total', [
            'header' => $this->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ]);
        $this->addColumn('order_status', [
            'header' => $this->__('Order Status'),
            'index' => 'order_status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ]);
        $this->addColumn('customer_firstname', [
            'header' => $this->__('First Name'),
            'align' => 'left',
            'index' => 'customer_firstname'
        ]);
        $this->addColumn('customer_lastname', [
            'header' => $this->__('Last Name'),
            'align' => 'left',
            'index' => 'customer_lastname'
        ]);
        $this->addColumn('customer_city', [
            'header' => $this->__('City'),
            'align' => 'left',
            'index' => 'customer_city'
        ]);
        $this->addColumn('customer_country', [
            'header' => $this->__('Country'),
            'align' => 'left',
            'width' => '30px',
            'index' => 'customer_country'
        ]);
        $this->addColumn('status', [
            'header' => $this->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::helper('gobeep_ecommerce')->getStatuses(),
        ]);
        $this->addColumn('created_at', [
            'header' => $this->__('Created at'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '160px',
        ]);
        $this->addColumn('updated_at', [
            'header' => $this->__('Updated at'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '160px',
        ]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', ['order_id' => $row->getId()]);
        }
        return false;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('order_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);
        $this->getMassactionBlock()->addItem('refund_gobeep_order', [
            'label' => Mage::helper('gobeep_ecommerce')->__('Change status'),
            'url'  => $this->getUrl('*/gobeep/massUpdateStatus'),
            'additional' => [
                'gobeep_status' => [
                    'name' => 'gobeep_status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'values' => Mage::helper('gobeep_ecommerce')->getStatuses()
                ]
            ]
        ]);

        return $this;
    }
}
