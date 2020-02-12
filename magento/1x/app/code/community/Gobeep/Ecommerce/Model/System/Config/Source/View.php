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
class Gobeep_Ecommerce_Model_System_Config_Source_View
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => Mage::helper('adminhtml')->__('Sunday')],
            ['value' => 1, 'label' => Mage::helper('adminhtml')->__('Monday')],
            ['value' => 2, 'label' => Mage::helper('adminhtml')->__('Tuesday')],
            ['value' => 3, 'label' => Mage::helper('adminhtml')->__('Wednesday')],
            ['value' => 4, 'label' => Mage::helper('adminhtml')->__('Thursday')],
            ['value' => 5, 'label' => Mage::helper('adminhtml')->__('Friday')],
            ['value' => 6, 'label' => Mage::helper('adminhtml')->__('Saturday')],
        ];
    }
}