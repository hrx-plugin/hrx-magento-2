<?php

namespace Hrx\Shipping\Model\Helper;

use HrxApi\API;
use HrxApi\Receiver;
use HrxApi\Shipment;
use HrxApi\Order;

class HrxApi {
    
    protected $api;
    protected $logger;
    protected $http_response_code;
    protected $http_error;

    public function init($secret, $logger, $test_mode = false) {
        $this->logger = $logger;
        if ($secret) {
            $this->api = new API($secret, $test_mode, false);
        } else {
            return false;
        }
    }

    public function getWarehouses() {
        try {
            return $this->api->getPickupLocations(1, 100);
        } catch (\Throwable $e) {
            //Log::debug($e->getMessage());
        }
        return [];
    }

    public function getTerminals() {
        $terminals = [];
        try {
            $page = 1;
            do {
                $data = $this->api->getDeliveryLocations($page, 200);
                if (is_array($data) && !empty($data)) {
                    $terminals = array_merge($terminals, $data);
                } else {
                    break;
                }
                //break;
                $page++;
            } while($data);
        } catch (\Throwable $e) {
            $this->logger->debug($e->getMessage());
        }
        return $terminals;
    }

    public function testCredentials() {
        try {
            $locs = $this->api->getPickupLocations(1, 1);
            return true;
        } catch (\Throwable $e) {

        }
        return false;
    }

    private function fixNumber($phone, $prefix) {
        $phone_cleaned = preg_replace("/[^0-9]/", "", $phone );
        $prefix_cleaned = preg_replace("/[^0-9]/", "", $prefix );
        if ($phone_cleaned && $prefix_cleaned && strripos($phone_cleaned, $prefix_cleaned) === 0) {
            return substr($phone_cleaned,strlen($prefix_cleaned), strlen($phone_cleaned));
        }
        return $phone;
    }

    public function createShipment($hrx_order, $order, $terminal) {
        if ($hrx_order->getStatus() != 'new') {
            throw new \Exception('Incorrect order status');
        }
        if (!$terminal) {
            throw new \Exception('Parcel terminal not selected');
        }
        /*** Create order ***/
        // Building receiver
        $shippingAddress = $order->getShippingAddress();
        $receiver = new Receiver();
        $receiver->setName($shippingAddress->getFirstname() . ' ' .  $shippingAddress->getLastname());
        $receiver->setEmail($shippingAddress->getEmail());
        $receiver->setPhone($this->fixNumber($shippingAddress->getTelephone(), $terminal->getPhonePrefix()), $terminal->getPhoneRegex());

        //Building shipment
        $shipment = new Shipment();
        $shipment->setReference('REF_MG' . $order->getIncrementId());
        $shipment->setComment('');
        $shipment->setLength((float)$hrx_order->getLength());
        $shipment->setWidth((float)$hrx_order->getWidth());
        $shipment->setHeight((float)$hrx_order->getHeight());
        $shipment->setWeight((float)$hrx_order->getWeight()); // kg

        //Building order
        $api_order = new Order();
        $api_order->setPickupLocationId($hrx_order->getHrxWarehouseId());
        $api_order->setDeliveryLocation($hrx_order->getHrxTerminalId());
        $api_order->setReceiver($receiver);
        $api_order->setShipment($shipment);
        $order_data = $api_order->prepareOrderData();

        //Sending order
        //var_dump($order_data); exit;
        $order_response = $this->api->generateOrder($order_data);
        $order_id = isset($order_response['id']) ? $order_response['id'] : false;
        //var_dump($order_response); exit;
        if ( $order_id ) {
            $hrx_order->setHrxOrderId($order_id);
            $hrx_order->setStatus('ready');

            //get newly created order to get tracking
            //get tracking number in cronjob
            /*
            $_order = $this->api->getOrder($order_id);
            //var_dump($_order);
            $order->tracking = $_order['tracking_number'] ?? null;
            */

            $hrx_order->save();
        } else {
            throw new \Exception('Failed to create order');
        }
    }

    public function getOrder($hrx_order) {
        if ($hrx_order->getHrxOrderId()) {
            return $this->api->getOrder($hrx_order->getHrxOrderId());
        }
        return [];
    }

    public function getLabel($hrx_order) {
        if ($hrx_order->getHrxOrderId()) {
            return $this->api->getLabel($hrx_order->getHrxOrderId());
        }
        return [];
    }

    public function getReturnLabel($hrx_order) {
        if ($hrx_order->getHrxOrderId()) {
            return $this->api->getReturnLabel($hrx_order->getHrxOrderId());
        }
        return [];
    }

    public function cancelOrder($hrx_order) {
        if ($hrx_order->getHrxOrderId()) {
            if (!in_array($hrx_order->getStatus(), ['new', 'ready', 'error'])) {
                throw new \Exception('Incorrect order status');
            }
            return $this->api->cancelOrder($hrx_order->getHrxOrderId());
        }
        return false;
    }

}