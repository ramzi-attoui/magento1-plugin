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
class Gobeep_Ecommerce_WebhookController extends Mage_Core_Controller_Front_Action
{
    /**
     * Accepts webhook requests from gobeep and redirects them to the suitable processor.
     */
    public function notifyAction()
    {
        $helper = Mage::helper('gobeep_ecommerce');
        $store = Mage::app()->getStore()->getStoreId();
        // Check if module is enabled
        if (!$helper->isModuleEnabled($store, false)) {
            $errors[] = 'Webhook is disabled, please come back later';
            $this->getResponse()
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(404);
            return $this;
        }

        // If request is not a POST request, return a 405 Method Not Allowed
        if (!$this->getRequest()->isPost()) {
            $errors[] = 'Webhook expects an HTTP POST';
            $this->getResponse()
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(405);
            return $this;
        }

        // Check if we have a X-Gobeep-Signature header in the HTTP request
        // If not, send a 400 Bad Request error
        $signature = $this->getRequest()->getHeader('x-gobeep-signature');
        if (!$signature) {
            $errors[] = 'Missing x-gobeep-signature header';
            $this->getResponse()
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(400);
            return $this;
        }

        // Verify signature, return a 403 if hashes doesn't match
        $body = $this->getRequest()->getRawBody();
        $digest = $helper->sign($body, $store);
        if ($signature !== $digest) {
            $errors[] = 'Signature doesn\'t match with incoming data';
            $this->getResponse()
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(403);
            return $this;
        }

        $request = new Gobeep_Ecommerce_Model_Webhook_Request();
        $errors = $request->processRefund(json_decode($body));

        if ($errors) {
            $this->getResponse()
                ->setBody(json_encode(['success' => false, 'errors' => $errors]))
                ->setHttpResponseCode(400);
            return $this;
        }

        // Succcess
        $this->getResponse()
            ->setBody(json_encode(['success' => true]))
            ->setHttpResponseCode(200);

        return $this;
    }

    /**
     * Predispatch: should set layout area
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        $this->setFlag('', self::FLAG_NO_START_SESSION, 1);
        parent::preDispatch();

        return $this;
    }

    /**
     * Postdispatch: should set last visited url
     *
     * @return Mage_Core_Controller_Front_Action
     */
    public function postDispatch()
    {
        return $this;
    }
}
