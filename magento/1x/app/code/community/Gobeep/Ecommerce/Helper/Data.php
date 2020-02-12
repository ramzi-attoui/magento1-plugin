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
class Gobeep_Ecommerce_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ACTIVE = 'sales/gobeep_ecommerce/active';
    const XML_PATH_CASHIER_URL = 'sales/gobeep_ecommerce/cashier_url';
    const XML_PATH_GAME_URL = 'sales/gobeep_ecommerce/game_url';
    const XML_PATH_SECRET = 'sales/gobeep_ecommerce/secret';
    const XML_PATH_FROM_DATE = 'sales/gobeep_ecommerce/from_date';
    const XML_PATH_TO_DATE = 'sales/gobeep_ecommerce/to_date';
    const XML_PATH_ELIGIBLE_DAYS = 'sales/gobeep_ecommerce/eligible_days';
    const XML_PATH_IMAGE = 'sales/gobeep_ecommerce/image';
    const XML_PATH_EXT_IMAGE = 'sales/gobeep_ecommerce/external_image';
    const XML_PATH_NOTIFY = 'sales/gobeep_ecommerce/notify';
    const XML_PATH_EMAIL_TEMPLATE = 'sales/gobeep_ecommerce/email_template';

    const XML_PATH_TIMEZONE = 'general/locale/timezone';

    /**
     * Checks if module is enabled by testing various parameters
     * of system configuration
     * 
     * @param int $store Store ID
     * @return bool
     */
    public function isModuleEnabled($store = null, $advancedCheck = true)
    {
        $isActive = Mage::getStoreConfig(self::XML_PATH_ACTIVE, $store);
        if (!$isActive) {
            return false;
        }

        $fromDate = Mage::getStoreConfig(self::XML_PATH_FROM_DATE, $store);
        $toDate = Mage::getStoreConfig(self::XML_PATH_TO_DATE, $store);
        $eligibleDays = Mage::getStoreConfig(self::XML_PATH_ELIGIBLE_DAYS, $store);
        $timezone = Mage::getStoreConfig(self::XML_PATH_TIMEZONE, $store);

        // Check if we have secret
        $secret = Mage::getStoreConfig(self::XML_PATH_SECRET, $store);
        if (!$secret) {
            return false;
        }
        // Check if date is in range
        if (!$this->isDateInRange($fromDate, $toDate, $timezone)) {
            return false;
        }

        // These parameters should not only be checked in frontend context
        // They are not required in webhook context
        if ($advancedCheck) {
            $url = Mage::getStoreConfig(self::XML_PATH_CASHIER_URL, $store);
            // Check if we have URL, remove trailing slash if there's one
            $url = rtrim(trim($url), '/');
            if ($url === '') {
                return false;
            }
            $url = Mage::getStoreConfig(self::XML_PATH_GAME_URL, $store);
            // Check if we have URL, remove trailing slash if there's one
            $url = rtrim(trim($url), '/');
            if ($url === '') {
                return false;
            }
            // Check if we have internal or external image
            $image = Mage::getStoreConfig(self::XML_PATH_IMAGE, $store);
            $externalImage = Mage::getStoreConfig(self::XML_PATH_EXT_IMAGE, $store);

            if (empty($image) && empty($externalImage)) {
                return false;
            }
            return $this->isDayEligible($eligibleDays, $timezone);
        }

        return true;
    }

    /**
     * Generates a querystring out of the array passed in parameter
     * encrypts the string generated with the public key stored in system/config
     * and returns a querystring
     * 
     * @param array $payload Array of parameters to sign
     * @param int   $store   Store ID
     * @return string|null
     */
    public function generateCashierLink($payload, $store)
    {
        $querystring = http_build_query($payload);
        $url = Mage::getStoreConfig(self::XML_PATH_CASHIER_URL, $store);

        $payload['signature'] = self::sign($querystring, $store);

        return $url . '?' . http_build_query($payload);
    }

    /**
     * Returns either an external or internal image
     * based on system configuration
     * 
     * @param int $store Store ID
     * @return string
     */
    public function getImage($store = null)
    {
        $image = Mage::getStoreConfig(self::XML_PATH_IMAGE, $store);
        $externalImage = Mage::getStoreConfig(self::XML_PATH_EXT_IMAGE, $store);

        if (!empty($externalImage)) {
            return $externalImage;
        }

        return sprintf(
            '%stheme/%s',
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA),
            $image
        );
    }

    /**
     * Returns possible module statuses
     * 
     * @return array
     */
    public function getStatuses()
    {
        return [
            Gobeep_Ecommerce_Model_Refund::STATUS_PENDING => $this->__('Pending'),
            Gobeep_Ecommerce_Model_Refund::STATUS_REFUNDED => $this->__('Refunded'),
        ];
    }

    /**
     * Checks if current date/time is in the range of
     * start date/end date stored in system configuration
     * 
     * The method takes the timezone stored in magento configuration 
     * into account, if no start date/end date is defined it picks a date
     * in the far past/future
     * 
     * 
     * @param string $startDate    Start Date
     * @param string $endDate      End Date
     * @param string $timezone     Timezone
     * @return bool
     */
    protected function isDateInRange($startDate, $endDate, $timezone)
    {
        if (empty($startDate)) {
            $startDate = '1970-01-01';
        }
        if (empty($endDate)) {
            $endDate = date('Y-m-d', strtotime('+10 years'));
        }

        // Convert to timestamp
        $startDate = new DateTime($startDate, new DateTimeZone($timezone));
        $endDate = new DateTime($endDate . ' 23:59:59', new DateTimeZone($timezone));
        $currentDate = new DateTime(date('Y-m-d H:i:s', strtotime('now')), new DateTimeZone($timezone));

        // Convert to UTC timezones
        $startDate->setTimezone(new DateTimeZone('UTC'));
        $endDate->setTimezone(new DateTimeZone('UTC'));
        $currentDate->setTimezone(new DateTimeZone('UTC'));

        // Convert to timestamp representation
        $startDateTs = $startDate->format('U');
        $endDateTs = $endDate->format('U');
        $currentDateTs = $currentDate->format('U');

        // Check that user date is between start & end, exit if not in range
        return (($currentDateTs >= $startDateTs) && ($currentDateTs <= $endDateTs));
    }

    /**
     * Checks if current day is eligible based on
     * days stored in system configuration
     * 
     * The method takes the timezone stored in magento configuration 
     * into account
     *
     * @param array  $eligibleDays Eligible Days
     * @param string $timezone     Timezone
     * @return bool
     */
    protected function isDayEligible($eligibleDays, $timezone)
    {
        $currentDate = new DateTime(date('Y-m-d H:i:s', strtotime('now')), new DateTimeZone($timezone));
        $currentDate->setTimezone(new DateTimeZone('UTC'));

        // If date is in range, check if day of week is eligible
        $dayOfWeek = intval($currentDate->format('w'));
        $eligibleDays = explode(',', $eligibleDays);

        return in_array($dayOfWeek, $eligibleDays);
    }

    /**
     * Signs a querystring with hash_hmac (SHA256)
     * 
     * @param string $text Text to sign
     * @return string
     */
    public static function sign($text, $store)
    {
        $secret = Mage::getStoreConfig(self::XML_PATH_SECRET, $store);
        return base64_encode(hash_hmac('sha256', $text, $secret, true));
    }
}
