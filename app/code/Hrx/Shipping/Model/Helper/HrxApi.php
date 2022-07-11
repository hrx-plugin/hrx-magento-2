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

    public function createShipment($order, $shopify_order_address) {
        if (!isset($shopify_order_address['data']['order'])) {
            throw new \Exception('Failed to get order address');
        }
        if ($order->status != 'new') {
            throw new \Exception('Incorrect order status');
        }
        /*** Create order ***/
        // Building receiver
        $receiver = new Receiver();
        $receiver->setName($shopify_order_address['data']['order']['shippingAddress']['name'] ?? '');
        $receiver->setEmail($shopify_order_address['data']['order']['email'] ?? '');
        $receiver->setPhone($this->fixNumber($shopify_order_address['data']['order']['shippingAddress']['phone'] ?? '', $order->terminal->phone_prefix), $order->terminal->phone_regex ?? '');

        //Building shipment
        $shipment = new Shipment();
        $shipment->setReference('REF_' . $order->shopify_order_id);
        $shipment->setComment('');
        $shipment->setLength((float)$order->length);
        $shipment->setWidth((float)$order->width);
        $shipment->setHeight((float)$order->height);
        $shipment->setWeight((float)$order->weight); // kg

        //Building order
        $api_order = new Order();
        $api_order->setPickupLocationId($order->warehouse ? $order->warehouse->warehouse_id : null);
        $api_order->setDeliveryLocation($order->terminal ? $order->terminal->terminal_id : null);
        $api_order->setReceiver($receiver);
        $api_order->setShipment($shipment);
        $order_data = $api_order->prepareOrderData();

        //Sending order
        //var_dump($order_data); exit;
        $order_response = $this->api->generateOrder($order_data);
        $order_id = isset($order_response['id']) ? $order_response['id'] : false;
        //var_dump($order_response);
        if ( $order_id ) {
            $order->order_id = $order_id;
            $order->status = 'ready';

            //get newly created order to get tracking
            //get tracking number in cronjob
            /*
            $_order = $this->api->getOrder($order_id);
            //var_dump($_order);
            $order->tracking = $_order['tracking_number'] ?? null;
            */

            $order->save();
        } else {
            throw new \Exception('Failed to create order');
        }
    }

    public function getOrder($order) {
        if ($order->order_id) {
            return $this->api->getOrder($order->order_id);
        }
        return [];
    }

    public function getLabel($order) {
        if ($order->order_id) {
            return $this->api->getLabel($order->order_id);
        }
        return [];
    }

}