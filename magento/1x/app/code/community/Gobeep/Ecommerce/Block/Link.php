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

class Gobeep_Ecommerce_Block_Link extends Mage_Core_Block_Template
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->setTemplate('gobeep/link.phtml');
        parent::_construct();
    }

    /**
     * Checks if a link can be generated based on system configuration
     * parameters
     * 
     * @return bool
     */
    public function canLink()
    {
        if (!$this->hasData('order') ||
            !is_a($this->getData('order'), 'Mage_Sales_Model_Order')
        ) {
            return false;
        }

        $order = $this->getData('order');
        $storeId = $order->getStoreId();

        $helper = Mage::helper('gobeep_ecommerce');
        if (!$helper->isModuleEnabled($storeId)) {
            return false;
        }

        $orderAmount = $order->getGrandTotal();
        if ($total === 0) {
          return false;
        }

        return true;
    }

    /**
     * Returns the image associated to the link
     * 
     * @return string
     */
    public function getImage()
    {
        $order = $this->getData('order');
        $storeId = $order->getStoreId();
        $helper = Mage::helper('gobeep_ecommerce');

        return $helper->getImage($storeId);
    }

    /**
     * Returns the link
     * 
     * @return string
     */
    public function getLink()
    {        
        $link = Mage::getModel('gobeep_ecommerce/link')
            ->fromOrderModel($this->getData('order'));

        return $link;
    }
}
