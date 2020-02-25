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
class Gobeep_Ecommerce_Model_Webhook_Request extends Mage_Core_Model_Abstract
{
    /**
     * Takes a refund JSON payload, validates incoming data and adds a new refund to the model
     * 
     * @param stdclass $payload
     * @return array|null
     */
    public function processRefund($payload)
    {
        $helper = Mage::helper('gobeep_ecommerce');
        $refund = Mage::getModel('gobeep_ecommerce/refund');
        $refund->addData([
            'status' => Gobeep_Ecommerce_Model_Refund::STATUS_PENDING,
            'order_id' => $payload->orderId,
            'created_at' => $payload->createdAt,
            'updated_at' => $payload->createdAt,
            'refund_email_sent' => 0,
            'winning_email_sent' => 0
        ]);

        $errors = [];
        if (!Zend_Validate::is($refund->getOrderId(), 'NotEmpty')) {
            $errors[] = $helper->__('`orderId` can\'t be empty');
        } else {
            // Check if refund already exists
            $existingRefund = Mage::getModel('gobeep_ecommerce/refund')
                ->load($refund->getOrderId());
            if ($existingRefund->getId()) {
                return null;
            }
            // Check if order exists
            $order = Mage::getModel('sales/order')->load($refund->getOrderId());
            if (!$order->getData()) {
                $errors[] = $helper->__('Order doesn\'t exist');
            } else {
                $refund->setOrderIncrementId($order->getIncrementId());
            }
        }

        //$validator = new Zend_Validate_Date(['format' => 'dd-mm-yyyy H:i:s']);
        if (!Zend_Date::isDate($refund->getCreatedAt(), Zend_Date::RFC_3339)) {
            $errors[] = $helper->__('`createdAt` is not a valid date');
        }

        if ($errors) {
            return $errors;
        }

        $refund->save(false);
        $refund->sendStatusNotification();

        Mage::dispatchEvent('gobeep_ecommerce_adminhtml_webhook_refund', [$refund]);

        return null;
    }
}
