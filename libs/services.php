<?php

require('libs/services/faucetfly.php');
require('libs/services/faucetpay.php');

class Service {
    public static $services = array(
        'faucetfly' => array(
            'name' => 'FaucetFly.com',
            'currencies' => array('BTC', 'DOGE', 'ETH', 'LTC')
        ),
        'faucetpay' => array(
            'name' => 'FaucetPay.io',
            'currencies' => array('BTC', 'BCH', 'BNB', 'DASH', 'DGB', 'DOGE', 'ETH', 'FEY', 'LTC', 'MATIC', 'SOL', 'TRX', 'USDT', 'XRP', 'ZEC')
        )
    );
    protected $service;
    protected $api_key;
    protected $user_token;
    protected $service_instance;
    protected $currency;
    public $communication_error = false;
    public $curl_warning = false;

    public $options = array(
        /* if disable_curl is set to true, it'll use PHP's fopen instead of
         * curl for connection */
        'disable_curl' => false,

        /* do not use these options unless you know what you're doing */
        'local_cafile' => false,
        'force_ipv4' => false,
        'verify_peer' => true
    );

    public function __construct($service, $api_key, $currency = 'BTC', $user_token = '', $connection_options = null) {
        $this->service = $service;
        $this->api_key = $api_key;
        $this->user_token = $user_token;
        $this->currency = $currency;
        if ($connection_options) {
            $this->options = array_merge($this->options, $connection_options);
        }

        switch ($this->service) {
            case 'faucetfly':
            case 'faucetpay':
                break;
            default:
                $this->service = 'faucetpay';
                break;
        }

        switch ($this->service) {
            case 'faucetfly':
                $this->service_instance = new FaucetFly($api_key, $currency, $connection_options);
                break;
            case 'faucetpay':
                $this->service_instance = new FaucetPay($api_key, $currency, $connection_options);
                break;
            default:
                trigger_error('Invalid service ' . $service);
        }
    }

    public function getServices($currency = null) {
        if (!$currency) {
            $all_services = [];
            foreach (self::$services as $service => $details) {
                $all_services[$service] = $details['name'];
            }
            return $all_services;
        }

        $services = [];
        foreach (self::$services as $service => $details) {
            if (in_array($service, $details['currencies'])) {
                $services[$service] = $details['name'];
            }
        }

        return $services;
    }

    public function send($to, $amount, $userip, $referral = 'false') {
        if ($this->currency === 'DOGE') {
            $amount *= 100000000;
        }
        switch ($this->service) {
            case 'faucetfly':
                $r = $this->service_instance->send($to, $amount, $userip, $referral);
                $check_url = 'https://www.faucetfly.com/check/' . rawurlencode($to);
                $success = $r['success'];
                if (!empty($r['balance'])) {
                    $balance = $r['balance'];
                } else {
                    $balance = null;
                }
                $error = $r['message'];
                $this->communication_error = $this->service_instance->communication_error;
                $this->curl_warning = $this->service_instance->curl_warning;
                break;
            case 'faucetpay':
                $r = $this->service_instance->send($to, $amount, $userip, $referral);
                $check_url = 'https://faucetpay.io/page/user-admin';
                $success = $r['success'];
                if (!empty($r['balance'])) {
                    $balance = $r['balance'];
                } else {
                    $balance = null;
                }
                $error = $r['message'];
                $this->communication_error = $this->service_instance->communication_error;
                $this->curl_warning = $this->service_instance->curl_warning;
                break;
        }

        $sname = self::$services[$this->service]['name'];
        $result = [];
        $result['success'] = $success;
        $result['response'] = json_encode($r);
        if ($success) {
            if (!empty($r['user_hash'])) {
                $result['user_hash'] = $r['user_hash'];
            }
            $result['message'] = 'Payment sent to you using ' . $sname;
            if (!empty($check_url)) {
                $result['html'] = '<div class="alert alert-success">' . htmlspecialchars($amount) . " satoshi was sent to you <a target=\"_blank\" href=\"$check_url\">on $sname</a>.</div>";
                $result['html_coin'] = '<div class="alert alert-success">' . htmlspecialchars(rtrim(rtrim(sprintf("%.8f", $amount / 100000000), '0'), '.')) . " " . $this->currency . " was sent to you <a target=\"_blank\" href=\"$check_url\">on $sname</a>.</div>";
            } else {
                $result['html'] = '<div class="alert alert-success">' . htmlspecialchars($amount) . " satoshi was sent to you on $sname.</div>";
                $result['html_coin'] = '<div class="alert alert-success">' . htmlspecialchars(rtrim(rtrim(sprintf("%.8f", $amount / 100000000), '0'), '.')) . " " . $this->currency . " was sent to you on $sname.</div>";
            }
            $result['balance'] = $balance;
            if ($balance) {
                $result['balance_bitcoin'] = sprintf("%.8f", $balance / 100000000);
            } else {
                $result['balance_bitcoin'] = null;
            }
        } else {
            $result['message'] = $error;
            $result['html'] = '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
        }
        return $result;
    }

    public function sendReferralEarnings($to, $amount, $userip) {
        return $this->send($to, $amount, $userip, 'true');
    }

    public function getPayouts($count) {
        return [];
    }

    public function getCurrencies() {
        return self::$services[$this->service]['currencies'];
    }

    public function getBalance() {
        switch ($this->service) {
            case 'faucetfly':
                $balance = $this->service_instance->getBalance();
                $this->communication_error = $this->service_instance->communication_error;
                $this->curl_warning = $this->service_instance->curl_warning;
                return $balance;
            case 'faucetpay':
                $balance = $this->service_instance->getBalance();
                $this->communication_error = $this->service_instance->communication_error;
                $this->curl_warning = $this->service_instance->curl_warning;
                return $balance;
        }
        die('Database is broken. Please reinstall the script.');
    }

    public function checkHash($to) {
        switch ($this->service) {
            case 'faucetfly':
                $hash = $this->service_instance->checkHash($to);
                $this->communication_error = $this->service_instance->communication_error;
                $this->curl_warning = $this->service_instance->curl_warning;
                return $hash;
            case 'faucetpay':
                $hash = $this->service_instance->checkHash($to);
                $this->communication_error = $this->service_instance->communication_error;
                $this->curl_warning = $this->service_instance->curl_warning;
                return $hash;
        }
        die('Database is broken. Please reinstall the script.');
    }

    public function fiabVersionCheck() {
        return 0;
    }
}
