<?php if(!defined("IN_ESOTALK")) exit;
/**
 * OpauthConnect
 * 
 * @copyright Copyright © 2012 Oleksandr Golubtsov
 * @license   GPLv2 License
 * @package   OpauthСonnect
 * 
 * This file is part of OpauthСonnect plugin. Please see the included license file for usage information
 */

require_once "Opauth".DIRECTORY_SEPARATOR."Opauth.php";

final class OpauthConnect {
    const ACCOUNT_CONFIRMED = 'accConfirmed';
    const ACCOUNT_NOT_CONFIRMED = 'accNotConfirmed';
    const ACCOUNT_NOT_EXISTS = 'accNotExists';
    
    /**
     * Interval in seconds
     */
    const CONFIRMATION_INTERVAL = 300;
    
    const PASSWORD_CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    
    const CONFIG_SALT = 'security_salt';
    const CONFIG_PATH = 'path';
    const CONFIG_CALLBACK = 'callback_url';
    const CONFIG_STRATEGY = 'Strategy';
    
    private $_config = array();
    
    public function __construct($config) {
        $this->_config = $config;
    }
    
    public function doRequest() {
        new Opauth($this->_config);
    }
    
    public function getResponse() {
        $response = $this->validateResponse();
        return array(
            "static" => array(
                "email"    => isset($response['auth']['info']['email']) ? $response['auth']['info']['email'] : null,
                "provider" => strtolower($response['auth']['provider']),
                "uid"      => strtolower($response['auth']['uid']),
                "link"     => $this->getProfileLink($response),
                "name"     => $this->getFullName($response)
            ),
            "editable" => array(
                "email"    => isset($response['auth']['info']['email']) ? $response['auth']['info']['email'] : null,
                "avatar"   => isset($response['auth']['info']['image']) ? $response['auth']['info']['image'] : null,
                "username" => $this->getFullName($response)
            )            
        );
    }
    
    private function validateResponse() {
        $Opauth = new Opauth($this->_config, false);
        $response = null;
        switch($Opauth->env['callback_transport']) {	
            case 'session':
                    $response = ET::$session->get('opauth');
                    ET::$session->remove('opauth');
                    break;
            case 'post':
                    $response = unserialize(base64_decode($_POST['opauth']));
                    break;
            case 'get':
                    $response = unserialize(base64_decode($_GET['opauth']));
                    break;
            default:
                    throw new Exception(T('Unsupported callback_transport'));
        }
        
        if(array_key_exists('error', $response)) {
            $error = json_decode($response['error']['raw']);
            throw new Exception(T('Authentication error: ').$error->errors[0]->message);
        }
        else {
            if(empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])){
                    throw new Exception(T('Invalid auth response: Missing key auth response components'));
            }
            elseif(!$Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)){
                    throw new Exception(T('Invalid auth response: ').$reason);
            }
            else {
                if(empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])){
                        throw new Exception(T('Invalid auth response: Missing key auth response components'));
                }
                elseif(!$Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)){
                        throw new Exception(T('Invalid auth response: ').$reason);
                }
                else {
                    return $response;
                }
            }
        }
    }
    
    private function getProfileLink($response) {
        $link = null;
        switch($response['auth']['provider']) {
            case 'Google':
                $link = $response['auth']['raw']['link'] ? $response['auth']['raw']['link'] : 'http://gmail.com';
                break;
            case 'Twitter':
                $link = $response['auth']['info']['urls']['twitter'];
                break;
            case 'Facebook':
                $link = $response['auth']['info']['urls']['facebook'];
                break;
            case 'VKontakte':
                $link = 'http://vk.com/id'.$response['auth']['uid'];
                break;
        }
        return $link;
    }
    
    private function getFullName($response) {
        if($response['auth']['provider'] == 'VKontakte') {
            $name = $response['auth']['raw']['first_name'].' '.$response['auth']['raw']['last_name'];
        }
        else {
            $name = $response['auth']['info']['name'];
        }
        return $name;
    }
    
}