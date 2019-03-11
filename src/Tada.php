<?php

namespace Bagus\Tada;

use Requests;

class Tada
{
	var $apiKey;
    var $apiSecret;
	var $username;
    var $password;
    var $url;
    var $token;

    public function __construct($apiKey, $apiSecret, $username, $password, $url)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->username = $username;
        $this->password = $password;
        $this->url = $url;
        return $this;
    }

    private function _getToken()
    {
        $header = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret)
        ];
        
        $body = [
            'username' => $this->username,
            'password' => $this->password,
            'grant_type' => 'password',
            'scope' => 'offline_access'
        ];

        $response = Requests::post($this->url . '/oauth/token', $header, json_encode($body));
        
        if (isset($response->body)) {
            $decode = json_decode($response->body);
            $this->token = $decode->access_token;
        }
    }
    
    private function _getHeader()
    {
        if (empty($this->token)) {
            $this->_getToken();
        }
        
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ];
    }
    
    public function cardsByPhone($phone)
    {
        if (empty($this->token)) {
            $this->_getToken();
        }
        
        $header = $this->_getHeader();

        $body = [
            'phone' => $phone,
            'action' => 'redemption'
        ];

        $response = Requests::post($this->url . '/v1/giftcards/card_by_phone', $header, json_encode($body));
        
        if (isset($response->body)) {
            return json_decode($response->body);
        }
    }
    
    public function reserveMembership($body)
    {
        if (empty($this->token)) {
            $this->_getToken();
        }
        
        $header = $this->_getHeader();
        
        $response = Requests::post($this->url . '/v1/membership_reward_program/reward/register', $header, json_encode($body));
        
        if (isset($response->body)) {
            return json_decode($response->body);
        }
    }

    public function cardDetail($cardNo)
    {
        if (empty($this->token)) {
            $this->_getToken();
        }
        
        $header = $this->_getHeader();

        $response = Requests::get($this->url . '/v1/card_managements/detail/' . $cardNo, $header);
        if (isset($response->body)) {
            return json_decode($response->body);
        }
    }

    public function balanceRedemption($cardNo, $amount)
    {
        if (empty($this->token)) {
            $this->_getToken();
        }
        
        $header = $this->_getHeader();
        
        $response = Requests::post($this->url . '/v1/giftcards/redeem', $header, json_encode([
            'distributionId' => $cardNo,
            'amount' => $amount
        ]));
        
        if (isset($response->body)) {
            return json_decode($response->body);
        }
    }

}
