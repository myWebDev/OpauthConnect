<?php
/**
 * OpauthConnect
 * 
 * @copyright Copyright © 2012 Oleksandr Golubtsov
 * @license   GPLv2 License
 * @package   OpauthСonnect
 * 
 * This file is part of OpauthСonnect plugin. Please see the included license file for usage information
 */
?>

<script>
    $(document).ready(function() {
        $(":checkbox:not(.static)").each(function() {
            toggleFields(this);
        })
    });
</script>

<?php print $data["form"]->open(); ?>
    <div class='section opauth-settings clearfix'>

        <div class="category first-category">
            <div class="row category-toggle">
                Common settings <span>+</span><span style="display: none;">-</span>
            </div>
            <div class="category-settings">
                <div class="row clearfix">
                    <div class="status">
                        <label><?php print T('Security salt'); ?></label>
                    </div>
                    <div>
                        <ul class='form'>
                            <li>
                                <?php print $data["form"]->input("security_salt", "text"); ?>
                                <div class="help"><?php print T('Strongly recommend to set your own value!'); ?></div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="status">
                        <?php print $data["form"]->checkbox("allow_unlink", array("class" => "static")); ?>
                        <label><?php print T('Unlink accounts'); ?></label>
                    </div>
                    <div>
                        <?php print T('Allows users to unlink their social accounts.'); ?><br/>
                        <?php print T('Without removing forum account, for sure.'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="category">
            <div class="row category-toggle">
                Social networks settings <span>+</span><span style="display: none;">-</span>
            </div>
            <div class="category-settings">
                <div class="row clearfix">
                    <div class="status">
                        <?php print $data["form"]->checkbox("strategy[tw]"); ?>
                        <label>Twitter</label>
                    </div>
                    <div>
                        <ul class='form'>
                            <li>
                                <label>Twitter Key</label>
                                <?php print $data["form"]->input("twitter_key", "text"); ?>
                            </li>

                            <li>
                                <label>Twitter Secret</label>
                                <?php print $data["form"]->input("twitter_secret", "text"); ?>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="row clearfix">
                    <div class="status">
                        <?php print $data["form"]->checkbox("strategy[fb]"); ?>
                        <label>Facebook</label>
                    </div>
                    <div>
                        <ul class='form'>
                            <li>
                                <label>Facebook App ID</label>
                                <?php print $data["form"]->input("facebook_key", "text"); ?>
                            </li>

                            <li>
                                <label>Facebook App Secret</label>
                                <?php print $data["form"]->input("facebook_secret", "text"); ?>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="row clearfix">
                    <div class="status">
                        <?php print $data["form"]->checkbox("strategy[gg]"); ?>
                        <label>Google</label>
                    </div>
                    <div>
                        <ul class='form'>
                            <li>
                                <label>Google Client ID</label>
                                <?php print $data["form"]->input("google_key", "text"); ?>
                            </li>

                            <li>
                                <label>Google Client Secret</label>
                                <?php print $data["form"]->input("google_secret", "text"); ?>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="row clearfix">
                    <div class="status">
                        <?php print $data["form"]->checkbox("strategy[vk]"); ?>
                        <label>VKontakte</label>
                    </div>
                    <div>
                        <ul class='form'>
                            <li>
                                <label>VKontakte Key</label>
                                <?php print $data["form"]->input("vkontakte_key", "text"); ?>
                            </li>

                            <li>
                                <label>VKontakte Secret</label>
                                <?php print $data["form"]->input("vkontakte_secret", "text"); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="category">
            <div class="row category-toggle">
                Emails settings <span>+</span><span style="display: none;">-</span>
            </div>
            <div class="category-settings">
                <div class="row clearfix">
                    <div class="status">
                        <label><?php print T('Confrimation email subject'); ?></label>
                    </div>
                    <div>
                        <ul class='form'>
                            <li>
                                <?php print $data["form"]->input("confirmation_title", "textarea", array("rows" => 3)); ?>
                                <div class="help">
                                    <?php print T('Available wildcards:'); ?><br/>
                                    [forumName] - <?php print T("Forum title"); ?><br/>
                                    [userName] - <?php print T("Forum username"); ?><br/>
                                    [socialNetwork] - <?php print T("User's social network"); ?><br/>
                                    [socialName] - <?php print T("User's name in this network"); ?>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="status">
                        <label><?php print T('Password email subject'); ?></label>
                    </div>
                    <div>
                        <ul class='form'>
                            <li>
                                <?php print $data["form"]->input("password_email_title", "textarea", array("rows" => 3)); ?>
                                <div class="help">
                                    <?php print T('Available wildcards:'); ?><br/>
                                    [forumName] - <?php print T("Forum title"); ?><br/>
                                    [userName] - <?php print T("Forum username"); ?>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class='buttons'>
        <?php print $data["form"]->saveButton(); ?>
    </div>
<?php print $data["form"]->close(); ?>