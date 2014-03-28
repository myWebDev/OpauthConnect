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

ET::$pluginInfo["OpauthConnect"] = array(
    "name" => "OpauthConnect",
    "description" => "Sign in via social networks",
    "version" => "2.0.1",
    "author" => "Oleksandr Golubtsov",
    "authorEmail" => "alex.8fmi@gmail.com",
    "authorURL" => "http://mikrobill.com",
    "license" => "GPLv2"
);

require_once "classes".DIRECTORY_SEPARATOR."OpauthConnect.php";
require_once "classes".DIRECTORY_SEPARATOR."OCSettings.php";

class ETPlugin_OpauthConnect extends ETPlugin {
    private $settings;
    private $opauth_connect;
    
    public function __construct($rootDirectory) {
        parent::__construct($rootDirectory);
        ETFactory::register("ocMemberSocialModel", "OcMemberSocialModel", dirname(__FILE__)."/models/OcMemberSocialModel.class.php");
        $this->settings = new OCSettings;
        
        $config = array();
        $config[OpauthConnect::CONFIG_SALT] = $this->settings->get(OCSettings::SECURITY_SALT);
        $config[OpauthConnect::CONFIG_PATH] = URL('user/social/');
        $config[OpauthConnect::CONFIG_CALLBACK] = URL('user/social/callback/');
        
        if($this->settings->get(OCSettings::TWITTER_ENABLED)) {
            $config[OpauthConnect::CONFIG_STRATEGY]['Twitter'] = array(
                'key' => $this->settings->get(OCSettings::TWITTER_KEY),
                'secret' => $this->settings->get(OCSettings::TWITTER_SECRET)
            );
        }
        
        if($this->settings->get(OCSettings::FACEBOOK_ENABLED)) {
            $config[OpauthConnect::CONFIG_STRATEGY]['Facebook'] = array(
                'app_id' => $this->settings->get(OCSettings::FACEBOOK_KEY),
                'app_secret' => $this->settings->get(OCSettings::FACEBOOK_SECRET),
                'scope' => 'email'
            );
        }
        
        if($this->settings->get(OCSettings::GOOGLE_ENABLED)) {
            $config[OpauthConnect::CONFIG_STRATEGY]['Google'] = array(
                'client_id' => $this->settings->get(OCSettings::GOOGLE_KEY),
                'client_secret' => $this->settings->get(OCSettings::GOOGLE_SECRET)
            );
        }
        
        if($this->settings->get(OCSettings::VK_ENABLED)) {
            $config[OpauthConnect::CONFIG_STRATEGY]['VKontakte'] = array(
                'app_id' => $this->settings->get(OCSettings::VK_KEY),
                'app_secret' => $this->settings->get(OCSettings::VK_SECRET)
            );
        }
        
        $this->opauth_connect = new OpauthConnect($config);
    }
    
    private function generateUsername($full_name, $email) {
        $full_name = str_replace(" ", "_", $full_name);
        if(ET::memberModel()->validateUsername($full_name) === null) {
            $username = $full_name;
        }
        else {
            $email = empty($email) ? $full_name : preg_replace("/[[:punct:]]/", "_", current(explode("@", $email)));
            $username = $email;
            while(ET::memberModel()->validateUsername($username) !== null) {
                $username = $email . "_" . rand(0, 999);
            }
        }
        
        return $username;
    }
    
    public function handler_init($sender) {
        if(ET::$session->get("remember") === null) ET::$session->store("remember", 1);
//            unset($sender->menus['user']->items['join']); //removes "sign in" button from header
        $sender->addCSSFile($this->getResource("css/opauthconnect.css"));
        $sender->addJSFile($this->getResource("js/opauthconnect.js"));
        ET::define("message.logInToReply", "<a href='%1\$s' class='link-login'>Log In</a> or <a href='%1\$s' class='link-login'>Sign Up</a> to reply!", false);
    }
    
    public function handler_renderOpauth($sender) {
        $data = array(
            'remember' => ET::$session->get("remember", 0),
            'services' => array()
        );
        if($this->settings->get(OCSettings::TWITTER_ENABLED)) {
            $data['services']['Twitter'] = array(
                'url' => URL('user/social/twitter'),
                'icon' => URL($this->getResource("images/twitter-thumb.gif"))
            );
        }
        if($this->settings->get(OCSettings::FACEBOOK_ENABLED)) {
            $data['services']['Facebook'] = array(
                'url' => URL('user/social/facebook'),
                'icon' => URL($this->getResource("images/facebook-thumb.png"))
            );
        }
        if($this->settings->get(OCSettings::GOOGLE_ENABLED)) {
            $data['services']['Google'] = array(
                'url' => URL('user/social/google'),
                'icon' => URL($this->getResource("images/google-thumb.png"))
            );
        }
        if($this->settings->get(OCSettings::VK_ENABLED)) {
            $data['services']['Vkontakte'] = array(
                'url' => URL('user/social/vkontakte'),
                'icon' => URL($this->getResource("images/vkontakte-thumb.png"))
            );
        }
        print $sender->getViewContents('social/oc_buttons', $data);
    }
    
    public function handler_settingsController_renderBefore($sender) {
        if(isset($sender->data['panes']) && !empty($sender->data['panes'])) {
            $sender->data['panes']->add('social_accounts', "<a href='".URL("settings/social/accounts")."'>".T("Social accounts")."</a>");
        }
    }
    
    private function login($memberId) {
        if($memberId) {
            ET::$session->loginWithMemberId($memberId);
            if(ET::$session->get("remember")) ET::$session->setRememberCookie($memberId);
        }
        redirect(URL());
    }
    
    public function settings($sender) {
        $sender->addCSSFile($this->getResource("css/backend.css"));
        $sender->addJSFile($this->getResource("js/backend.js"));
        
        $form = ETFactory::make("form");
        $form->action = URL("admin/plugins");

        if($form->validPostBack("save") && !$form->errorCount()) {
            $this->settings->set(OCSettings::TWITTER_ENABLED,    $form->getValue("strategy[tw]"));
            $this->settings->set(OCSettings::TWITTER_KEY,        $form->getValue("twitter_key"));
            $this->settings->set(OCSettings::TWITTER_SECRET,     $form->getValue("twitter_secret"));
            $this->settings->set(OCSettings::FACEBOOK_ENABLED,   $form->getValue("strategy[fb]"));
            $this->settings->set(OCSettings::FACEBOOK_KEY,       $form->getValue("facebook_key"));
            $this->settings->set(OCSettings::FACEBOOK_SECRET,    $form->getValue("facebook_secret"));
            $this->settings->set(OCSettings::GOOGLE_ENABLED,     $form->getValue("strategy[gg]"));
            $this->settings->set(OCSettings::GOOGLE_KEY,         $form->getValue("google_key"));
            $this->settings->set(OCSettings::GOOGLE_SECRET,      $form->getValue("google_secret"));
            $this->settings->set(OCSettings::SECURITY_SALT,      $form->getValue("security_salt"));
            $this->settings->set(OCSettings::ALLOW_UNLINK,       $form->getValue("allow_unlink"));
            $this->settings->set(OCSettings::CONFIRM_EMAIL_SUBJ, $form->getValue("confirmation_title"));
            $this->settings->set(OCSettings::PASS_EMAIL_SUBJ,    $form->getValue("password_email_title"));
            $this->settings->set(OCSettings::VK_ENABLED,         $form->getValue("strategy[vk]"));
            $this->settings->set(OCSettings::VK_KEY,             $form->getValue("vkontakte_key"));
            $this->settings->set(OCSettings::VK_SECRET,          $form->getValue("vkontakte_secret"));
            $this->settings->save();
            
            $sender->message(T("message.changesSaved"), "success");
            $sender->redirect(URL("admin/plugins"));
        }
        
        $form->setValue("strategy[tw]",         $this->settings->get(OCSettings::TWITTER_ENABLED));
        $form->setValue("twitter_key",          $this->settings->get(OCSettings::TWITTER_KEY));
        $form->setValue("twitter_secret",       $this->settings->get(OCSettings::TWITTER_SECRET));
        $form->setValue("strategy[fb]",         $this->settings->get(OCSettings::FACEBOOK_ENABLED));
        $form->setValue("facebook_key",         $this->settings->get(OCSettings::FACEBOOK_KEY));
        $form->setValue("facebook_secret",      $this->settings->get(OCSettings::FACEBOOK_SECRET));
        $form->setValue("strategy[gg]",         $this->settings->get(OCSettings::GOOGLE_ENABLED));
        $form->setValue("google_key",           $this->settings->get(OCSettings::GOOGLE_KEY));
        $form->setValue("google_secret",        $this->settings->get(OCSettings::GOOGLE_SECRET));
        $form->setValue("security_salt",        $this->settings->get(OCSettings::SECURITY_SALT));
        $form->setValue("allow_unlink",         $this->settings->get(OCSettings::ALLOW_UNLINK));
        $form->setValue("confirmation_title",   $this->settings->get(OCSettings::CONFIRM_EMAIL_SUBJ));
        $form->setValue("password_email_title", $this->settings->get(OCSettings::PASS_EMAIL_SUBJ));
        $form->setValue("strategy[vk]",         $this->settings->get(OCSettings::VK_ENABLED));
        $form->setValue("vkontakte_key",        $this->settings->get(OCSettings::VK_KEY));
        $form->setValue("vkontakte_secret",     $this->settings->get(OCSettings::VK_SECRET));

        $sender->data("form", $form);
        return $this->getView('admin/oc_settings');
    }
    
    public function settingsController_social($sender, $action, $param1 = null) {
        $sender->addCSSFile($this->getResource("css/backend.css"));
        $sender->addJSFile($this->getResource("js/backend.js"));
        switch($action) {
            case "accounts":
                $this->settings_social_accounts($sender);
                break;
            
            case "unlink":
                $this->settings_social_unlink_account($sender, $param1);
                break;
            
            default:
                $sender->render404();
                break;
        }
    }
    
    private function settings_social_accounts($sender) {
        $sender->dispatch("profile", array("social_accounts"));
        $accounts = ET::getInstance("ocMemberSocialModel")->getAccounts(ET::$session->userId);
        foreach($accounts as &$account) {
            $account["logo"] = URL($this->getResource("images/settings/".$account["socialNetwork"].".png"));
        }
        $sender->data("accounts", $accounts);
        $sender->data("allow_unlink", $this->settings->get(OCSettings::ALLOW_UNLINK));
        $sender->data("accounts_exists", empty($accounts) ? false : true);
        $sender->renderProfile($this->getView("admin/oc_social_accounts"));
    }
    
    private function settings_social_unlink_account($sender, $id) {
        if(!$this->settings->get(OCSettings::ALLOW_UNLINK) || !ET::getInstance("ocMemberSocialModel")->isApropriateUser(ET::$session->userId, $id)) {
            $sender->render404();
            return;
        }
        ET::getInstance("ocMemberSocialModel")->deleteById($id);
        redirect(URL("settings/social/accounts"));
    }
    
    public function setup($oldVersion = "") {
        ET::$database->structure()->table("member")
                                  ->dropColumn("fromSN")
                                  ->dropColumn("TWid")
                                  ->dropColumn("TWconfirmed")
                                  ->exec(false);
        ET::$database->structure()->table("oc_member_social")
                                  ->column("id", "int(11) unsigned", false)
                                  ->column("member_Id", "int(11) unsigned", false)
                                  ->column("socialNetwork", "varchar(255)", false)
                                  ->column("socialId", "varchar(255)", false)
                                  ->column("profileLink", "varchar(255)", false)
                                  ->column("name", "varchar(255)", false)
                                  ->column("confirmed", "tinyint unsigned", false)
                                  ->column("confirmationHash", "varchar(255)")
                                  ->column("confirmationSent", "int(11) unsigned", 0)
                                  ->key("id", "primary")
                                  ->key(array("socialNetwork","socialId"), "unique")
                                  ->exec(false);
        ET::$database->query(strtr("ALTER TABLE [prefix]oc_member_social ADD FOREIGN KEY(member_Id) REFERENCES [prefix]member(memberId)", array('[prefix]' => C("esoTalk.database.prefix"))));
        return true;
    }
    
    private function social_auth($sender) {
        if(ET::$session->user) {
            $sender->message( T('You are already logged in') );
            redirect(URL());
        }
        $this->opauth_connect->doRequest();
    }
    
    private function social_callback($sender) {
        try {
            $response = $this->opauth_connect->getResponse();
            switch(ET::getInstance("ocMemberSocialModel")->validateAccount($response["static"]['uid'], $response["static"]['provider'], $id)) {
                case OpauthConnect::ACCOUNT_CONFIRMED:
                    $this->login($id);
                    break;
                
                case OpauthConnect::ACCOUNT_NOT_EXISTS:
                    ET::$session->store("OpauthConnect", $response["static"]);
                    $response["editable"]["username"] = $this->generateUsername($response["editable"]["username"], $response["editable"]["email"]);
                    $this->social_setup($sender, $response["editable"]);
                    return;
                
                case OpauthConnect::ACCOUNT_NOT_CONFIRMED:
                    $sender->message("Your account was not confirmed. <a href='".URL("user/social/sendconfirmation/".$id)."'>Send confirmation letter again</a>", "warning");
                    break;
            }
        }
        catch(Exception $ex) {
            $sender->message($ex->getMessage(), "warning");
        }
        redirect(URL());
    }
    
    private function social_confirm($sender, $hash) {
        if($result = ET::getInstance("ocMemberSocialModel")->validateConfirmationHash($hash)) {
            $sender->message(T("You successfully confirmed your new account"), "success");
            $this->login($result);
        }
        $sender->message(T("Invalid confirmation hash"), "warning");
        redirect(URL());
    }
    
    private function social_remember() {
        ET::$session->store("remember", (int)R("remember", 0));
    }
    
    private function social_sendConfirmation($sender, $row_id, $is_new_user) {
        $data = ET::getInstance("ocMemberSocialModel")->getConfirmationData($row_id);
        if(time() - $data["confirmationSent"] > OpauthConnect::CONFIRMATION_INTERVAL) {
            $params = array(
                "confirmationUrl"  => URL("user/social/confirm/".$data['confirmationHash'], true),
                "profileLink"      => $data["profileLink"],
                "socialName"       => $data["name"],
                "socialNetwork"    => $data["socialNetwork"],
                "userName"         => $data["username"],
                "forumName"        => C("esoTalk.forumTitle"),
                "isNewUser"        => $is_new_user
            );

            $title = strtr($this->settings->get(OCSettings::CONFIRM_EMAIL_SUBJ), array(
                "[forumName]"     => C("esoTalk.forumTitle"),
                "[socialNetwork]" => $data["socialNetwork"],
                "[socialName]"    => $data["name"],
                "[userName]"      => $data["username"]
            ));
            
            sendEmail($data["email"], $title, $sender->getViewContents('emails/oc_confirmation', $params));
            
            ET::getInstance("ocMemberSocialModel")->sentConfirmation($row_id);
            $sender->message("Confirmation letter was sent to your e-mail address (".$data["email"].")", "success");
        }
        else {
            $sender->message(T("Confirmation letter can be sent once per 5 minutes. Please wait"), "warning");
        }
        redirect(URL());
    }
    
    private function social_setup($sender, $form_data = array()) {
        $session_data = ET::$session->get("OpauthConnect");
        if(!$session_data) {
            $sender->message(T("Time is out. Please, try again"), "warning");
            redirect(URL("user/login"));
        }
        
        $email_only = false;
        if(ET::memberModel()->validateEmail($session_data["email"]) == "emailTaken") {
            $email_only = true;
        }

        $form = ETFactory::make("form");
        $form->action = URL("user/social/setup");

        $sender->data("show_password", false);
        if($form->validPostBack("save")) {
            if(!$session_data["email"]) {
                if(!$form->getValue("email")) {
                    $form->error("email", T("Email must be set"));
                }
                if(ET::memberModel()->validateEmail($form->getValue("email"), false) !== null) {
                    $form->error("email", T("Email is invalid"));
                }
            }
            
            if(!$email_only) {
                if(!$form->getValue("generate_password")) {
                    if(ET::memberModel()->validatePassword($form->getValue("password")) !== null) {
                        $form->error("password", T("Password is too shot"));
                        $sender->data("show_password", true);
                    }
                    elseif($form->getValue("password") != $form->getValue("password_repeat")) {
                        $form->error("password_repeat", T("Passwords not match"));
                        $sender->data("show_password", true);
                    }
                }

                switch(ET::memberModel()->validateUsername($form->getValue("username"))) {
                    case 'nameTaken':
                        $form->error("username", T("Username is already exists"));
                        break;
                    case 'invalidUsername':
                        $form->error("username", T("Username is incorrect"));
                        break;
                }
            }

            if(!$form->errorCount()) {
                if($session_data["email"]) {
                    $email = $session_data["email"];
                    $needs_confirmation = false;
                }
                else {
                    $email = $form->getValue("email");
                    $needs_confirmation = true;
                }

                switch(ET::memberModel()->validateEmail($email)) {
                    case null:
                        $password = $form->getValue("generate_password") ? generateRandomString(12, OpauthConnect::PASSWORD_CHARS) : $form->getValue("password");
                        if($form->getValue("generate_password")) {
                            $params = array(
                                "userName" => $form->getValue("username"),
                                "password" => $password,
                                "forumName" => C("esoTalk.forumTitle")
                            );
                            
                            $title = strtr($this->settings->get(OCSettings::PASS_EMAIL_SUBJ), array(
                                "[forumName]" => C("esoTalk.forumTitle"),
                                "[userName]"  => $form->getValue("username")
                            ));
                            
                            sendEmail($email, $title, $sender->getViewContents('emails/oc_password', $params));
                        }

                        $data = array(
                            "username" => $form->getValue("username"),
                            "email" => $email,
                            "password" => $password,
                            "account" => ACCOUNT_MEMBER,
                            "resetPassword" => null,
                            "confirmedEmail" => 1 //this field is not used. just dummy value
                        );
                        $memberId = ET::memberModel()->create($data);
                        $avatar_exists = false;
                        $is_new_user = true;
                        break;
                        
                    case "emailTaken":
                        $member = ET::memberModel()->get(array("email" => $email));
                        $avatar_exists = $member[0]["avatarFormat"];
                        $memberId = $member[0]["memberId"];
                        $is_new_user = null;
                        break;
                }
                
                ET::$session->remove("OpauthConnect");
                if(!$avatar_exists && $form->getValue("avatar")) {
                    $avatar = ET::uploader()->saveAsImage($form->getValue("avatar"), PATH_UPLOADS."/avatars/".$memberId, C("esoTalk.avatars.width"), C("esoTalk.avatars.height"), "crop");
                    ET::memberModel()->updateById($memberId, array("avatarFormat" => pathinfo($avatar, PATHINFO_EXTENSION)));
                }
                
                $row_id = ET::getInstance("ocMemberSocialModel")->addAccount(
                        $memberId,
                        $session_data["provider"],
                        $session_data["uid"],
                        $session_data["link"],
                        $session_data["name"],
                        !$needs_confirmation,
                        $needs_confirmation ? md5(uniqid(rand())) : null
                );
                
                if($needs_confirmation) {
                    $this->social_sendConfirmation($sender, $row_id, $is_new_user);
                }
                else {
                    $this->login($memberId);
                }
            }
        }

        foreach($form_data as $key => $value) {
            $form->setValue($key, $value);
        }
        
        $sender->data("show_email", true);
        if($session_data["email"]) {
            if($form->isPostBack()) $_POST['email'] = $session_data["email"];
            else $form->setValue("email", $session_data["email"]);
            $sender->data("show_email", false);
        }
        
        $sender->data("email_only", $email_only);
        $sender->data("form", $form);
        $sender->render("social/oc_account_setup");
    }
    
    public function userController_social($sender, $action, $param1 = null, $param2 = null) {
        switch($action) {
            case "setup":
                $this->social_setup($sender);
                break;
            
            case "callback":
                $this->social_callback($sender);
                break;
            
            case "facebook":
            case "twitter":
            case "google":
            case "vkontakte":
                $this->social_auth($sender);
                break;
            
            case "sendconfirmation":
                $this->social_sendConfirmation($sender, $param1, $param2);
                break;
            
            case "confirm":
                $this->social_confirm($sender, $param1);
                break;
            
            case "remember":
                $this->social_remember();
                break;
            
            default:
                $sender->render404();
                break;
        }
    }
    
    /**
     * TODO: In newest version of esoTalk remove "plugin.opauthconnect.*" from config.
     * Now there is no way to do this.
     */
    public function uninstall() {}
}
