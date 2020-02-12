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
class Gobeep_Ecommerce_Model_Observer
{
    /**
     * Home identifier
     */
    const HOME_IDENT = 'home';

    /**
     * Before load layout event handler
     *
     * @param Varien_Event_Observer $observer
     */
    public function beforeLoadLayout($observer)
    {
        $routeName = Mage::app()->getRequest()->getRouteName();
        $identifier = Mage::getSingleton('cms/page')->getIdentifier();

        if ($routeName == 'cms' && $identifier == 'home') {
            // do something
        }

        $observer->getEvent()->getLayout()->getUpdate()
            ->addHandle('customer_logged_' . ($loggedIn ? 'in' : 'out'));
    }

    /**
     * Address before save event handler
     *
     * @param Varien_Event_Observer $observer
     */
    public function beforeAddressSave($observer)
    {
        if (Mage::registry(self::VIV_CURRENTLY_SAVED_ADDRESS)) {
            Mage::unregister(self::VIV_CURRENTLY_SAVED_ADDRESS);
        }

        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = $observer->getCustomerAddress();
        if ($customerAddress->getId()) {
            Mage::register(self::VIV_CURRENTLY_SAVED_ADDRESS, $customerAddress->getId());
        } else {
            $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();

            $forceProcess = ($configAddressType == Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING)
                ? $customerAddress->getIsDefaultShipping() : $customerAddress->getIsDefaultBilling();

            if ($forceProcess) {
                $customerAddress->setForceProcess(true);
            } else {
                Mage::register(self::VIV_CURRENTLY_SAVED_ADDRESS, 'new_address');
            }
        }
    }

    /**
     * Address after save event handler
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterAddressSave($observer)
    {
        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();

        if (
            !Mage::helper('customer/address')->isVatValidationEnabled($customer->getStore())
            || Mage::registry(self::VIV_PROCESSED_FLAG)
            || !$this->_canProcessAddress($customerAddress)
        ) {
            return;
        }

        try {
            Mage::register(self::VIV_PROCESSED_FLAG, true);

            /** @var $customerHelper Mage_Customer_Helper_Data */
            $customerHelper = Mage::helper('customer');

            if (
                $customerAddress->getVatId() == ''
                || !Mage::helper('core')->isCountryInEU($customerAddress->getCountry())
            ) {
                $defaultGroupId = $customerHelper->getDefaultCustomerGroupId($customer->getStore());

                if (!$customer->getDisableAutoGroupChange() && $customer->getGroupId() != $defaultGroupId) {
                    $customer->setGroupId($defaultGroupId);
                    $customer->save();
                }
            } else {

                $result = $customerHelper->checkVatNumber(
                    $customerAddress->getCountryId(),
                    $customerAddress->getVatId()
                );

                $newGroupId = $customerHelper->getCustomerGroupIdBasedOnVatNumber(
                    $customerAddress->getCountryId(),
                    $result,
                    $customer->getStore()
                );

                if (!$customer->getDisableAutoGroupChange() && $customer->getGroupId() != $newGroupId) {
                    $customer->setGroupId($newGroupId);
                    $customer->save();
                }

                if (!Mage::app()->getStore()->isAdmin()) {
                    $validationMessage = Mage::helper('customer')->getVatValidationUserMessage(
                        $customerAddress,
                        $customer->getDisableAutoGroupChange(),
                        $result
                    );

                    if (!$validationMessage->getIsError()) {
                        Mage::getSingleton('customer/session')->addSuccess($validationMessage->getMessage());
                    } else {
                        Mage::getSingleton('customer/session')->addError($validationMessage->getMessage());
                    }
                }
            }
        } catch (Exception $e) {
            Mage::register(self::VIV_PROCESSED_FLAG, false, true);
        }
    }

    /**
     * Revert emulated customer group_id
     *
     * @param Varien_Event_Observer $observer
     */
    public function quoteSubmitAfter($observer)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getQuote()->getCustomer();

        if (!Mage::helper('customer/address')->isVatValidationEnabled($customer->getStore())) {
            return;
        }

        if (!$customer->getId()) {
            return;
        }

        $customer->setGroupId(
            $customer->getOrigData('group_id')
        );
        $customer->save();
    }
}
