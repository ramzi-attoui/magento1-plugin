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
class Gobeep_Ecommerce_Adminhtml_GobeepController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('gobeep_ecommerce/adminhtml_refund'))
            ->renderLayout();

        return $this;
    }

    /**
     * Mass update status action
     * Meant to update status and send status notification if enabled
     * 
     * @return Mage_Adminhtml_Controller_Action
     */
    public function massUpdateStatusAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        $newStatus = $this->getRequest()->getParam('gobeep_status');

        $count = 0;
        foreach ($orderIds as $orderId) {
            $refund = Mage::getModel("gobeep_ecommerce/refund")->load($orderId);
            $currStatus = $refund->getStatus();
            if ($currStatus !== $newStatus) {
                $refund->setStatus($newStatus)->save();
                $refund->sendStatusNotification();
                $count++;
                Mage::dispatchEvent('gobeep_ecommerce_adminhtml_update_refund_status', [$refund]);
            }
        }

        if ($count > 0) {
            $this->_getSession()->addSuccess(
                $this->__("$count gobeep refund(s) status has been changed to $newStatus")
            );
        }

        $this->_redirect('/gobeep/');

        return $this;
    }

    /**
     * Initialize action
     * Meant to set the breadcrumbs and the active menu
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('sales/gobeep_ecommerce_refund')
            ->_title($this->__('Sales'))->_title($this->__('Gobeep Refunds'))
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('GoBeep'), $this->__('Refunds'));

        return $this;
    }

    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/gobeep_ecommerce_refund');
    }
}
