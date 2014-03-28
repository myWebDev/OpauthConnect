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

class OCSettings {
    const GOOGLE_ENABLED     = "google_enable";
    const GOOGLE_KEY         = "google_key";
    const GOOGLE_SECRET      = "google_secret";
    const TWITTER_ENABLED    = "twitter_enable";
    const TWITTER_KEY        = "twitter_key";
    const TWITTER_SECRET     = "twitter_secret";
    const FACEBOOK_ENABLED   = "facebook_enable";
    const FACEBOOK_KEY       = "facebook_key";
    const FACEBOOK_SECRET    = "facebook_secret";
    const VK_ENABLED         = "vkontakte_enable";
    const VK_KEY             = "vkontakte_key";
    const VK_SECRET          = "vkontakte_secret";
    const SECURITY_SALT      = "security_salt";
    const ALLOW_UNLINK       = "allow_unlink";
    const CONFIRM_EMAIL_SUBJ = "confirmation_email_title";
    const PASS_EMAIL_SUBJ    = "password_email_title";
    
    private $_config_prefix = "OpauthConnect.";
    private $_values = array();
    private $_defaults = array(
        self::SECURITY_SALT      => "9XN2LTFHGXYsTgLW33Fb",
        self::CONFIRM_EMAIL_SUBJ => "Wellcome to [forumName], [socialName]! Please, confirm your email address",
        self::PASS_EMAIL_SUBJ    => "Your new account for [forumName]"
    );
    
    public function get($name) {
        $value = C($this->_config_prefix.$name);
        if(!$value && isset($this->_defaults[$name])) {
            $value = $this->_defaults[$name];
        }
        return $value;
    }
    
    public function set($key, $value) {
        $this->_values[$this->_config_prefix.$key] = $value;
    }
    
    public function save() {
        ET::writeConfig($this->_values);
        $this->_values = array();
    }
    
    public function setAndSave($key, $value) {
        $this->set($key, $value);
        $this->save();
    }
}