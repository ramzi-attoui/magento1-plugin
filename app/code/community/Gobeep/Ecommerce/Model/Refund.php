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
class Gobeep_Ecommerce_Model_Refund extends Mage_Core_Model_Abstract
{
    /**
     * Statuses
     */
    const STATUS_PENDING  = 'pending';
    const STATUS_REFUNDED = 'refunded';

    protected function _construct()
    {
        $this->_init('gobeep_ecommerce/refund');
    }

    /**
     * Sends a status notification email to customer
     * 
     * @return bool
     */
    public function sendStatusNotification()
    {
        $storeId = $this->getStoreId();
        $notifyEnabled = Mage::getStoreConfig(Gobeep_Ecommerce_Helper_Data::XML_PATH_NOTIFY, $storeId);
        if (!$notifyEnabled) {
            return false;
        }

        try {
            $status = $this->getStatus();
            $template = ($status === self::STATUS_REFUNDED) ?
                Gobeep_Ecommerce_Helper_Data::XML_PATH_REFUND_EMAIL_TEMPLATE :
                Gobeep_Ecommerce_Helper_Data::XML_PATH_WINNING_EMAIL_TEMPLATE;

            // Check if emails have been already sent
            if (
                ($status === self::STATUS_REFUNDED && $this->getRefundEmailSent()) ||
                ($status === self::STATUS_PENDING && $this->getWinningEmailSent())
            ) {
                return false;
            }

            $emailTemplate = Mage::getStoreConfig($template, $storeId);
            if ($emailTemplate) {
                $mailer = Mage::getModel('core/email_template_mailer');
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($this->getCustomerEmail(), "{$this->getCustomerFirstname()} {$this->getCustomerLastname()}");
                $mailer->addEmailInfo($emailInfo);

                // Set all required params and send emails
                $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId));
                $mailer->setStoreId($storeId);
                $mailer->setTemplateId($emailTemplate);
                $mailer->setTemplateParams([
                    'customerFirstName' => $this->getCustomerFirstname(),
                    'customerLastName'  => $this->getCustomerLastname(),
                    'customerEmail'     => $this->getCustomerEmail(),
                    'order'             => Mage::getModel('sales/order')->load($this->getOrderId())
                ]);
                $mailer->send();

                $this->setData(($status === self::STATUS_REFUNDED) ? 'refund_email_sent' : 'winning_email_sent', true);
                $this->save();
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return true;
    }
}
