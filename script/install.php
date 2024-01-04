<?php

$db_updates = array(
    1 => array(
        "create table if not exists `" . $dbtable_prefix . "Settings` (
            `name` varchar(64) not null,
            `value` longtext not null,
            primary key(`name`)
        );",
        "CREATE TABLE if not exists `" . $dbtable_prefix . "IPs` (
            `ip` varchar(45) not null,
            `last_used` timestamp not null,
            primary key(`ip`)
        );",
        "CREATE TABLE if not exists `" . $dbtable_prefix . "Addresses` (
            `address` varchar(110) not null,
            `ref_id` int null,
            `last_used` timestamp not null,
            primary key(`address`)
        );",
        "CREATE TABLE if not exists `" . $dbtable_prefix . "Refs` (
            `id` int auto_increment not null,
            `address` varchar(110) not null unique,
            primary key(`id`)
        );",
        "CREATE TABLE if not exists `" . $dbtable_prefix . "Pages` (
            `id` int auto_increment not null,
            `url_name` varchar(50) not null unique,
            `name` varchar(255) not null,
            `html` text not null,
            primary key(`id`)
        );",
        "CREATE TABLE if not exists `" . $dbtable_prefix . "Sessions_Log` (
                                    `" . $dbtable_prefix . "Sessions_Log_time` int(11) NOT NULL DEFAULT '0',
                                    `" . $dbtable_prefix . "Sessions_Log_session_id` varchar(50) NOT NULL DEFAULT '',
                                    `" . $dbtable_prefix . "Sessions_Log_message` varchar(1024) NOT NULL DEFAULT '',
                                    KEY `" . $dbtable_prefix . "Sessions_Log_time` (`" . $dbtable_prefix . "Sessions_Log_time`),
                                    KEY `" . $dbtable_prefix . "Sessions_Log_session_id` (`" . $dbtable_prefix . "Sessions_Log_session_id`)
        );",
        "CREATE TABLE if not exists `" . $dbtable_prefix . "ABL_Log` (
                                        `" . $dbtable_prefix . "ABL_Log_id` int(11) NOT NULL AUTO_INCREMENT,
                                        `" . $dbtable_prefix . "ABL_Log_time` int(11) NOT NULL DEFAULT '0',
                                        `" . $dbtable_prefix . "ABL_Log_IP` varchar(50) NOT NULL DEFAULT '',
                                        `" . $dbtable_prefix . "ABL_Log_address` varchar(110) NOT NULL DEFAULT '',
                                        `" . $dbtable_prefix . "ABL_Log_address_ref` varchar(110) NOT NULL DEFAULT '',
                                        `" . $dbtable_prefix . "ABL_Log_status` enum('valid','invalid','possibly bot') NOT NULL DEFAULT 'invalid',
                                        `" . $dbtable_prefix . "ABL_Log_session_id` varchar(50) NOT NULL DEFAULT '',
                                        PRIMARY KEY (`" . $dbtable_prefix . "ABL_Log_id`),
                                        KEY `" . $dbtable_prefix . "ABL_Log_time` (`" . $dbtable_prefix . "ABL_Log_time`),
                                        KEY `" . $dbtable_prefix . "ABL_Log_session_id` (`" . $dbtable_prefix . "ABL_Log_session_id`),
                                        KEY `" . $dbtable_prefix . "ABL_Log_IP` (`" . $dbtable_prefix . "ABL_Log_IP`)
        );",
        "INSERT IGNORE INTO " . $dbtable_prefix . "Settings (name, value) VALUES
            ('apikey', ''),
            ('timer', '180'),
            ('rewards', '90*10, 10*50'),
            ('referral', '15'),
            ('solvemedia_challenge_key', ''),
            ('solvemedia_verification_key', ''),
            ('solvemedia_auth_key', ''),
            ('recaptcha_private_key', ''),
            ('recaptcha_public_key', ''),
            ('name', 'Faucet in a Box <sup>ultimate</sup>'),
            ('short', 'Just another Faucet in a Box <sup>ultimate</sup>'),
            ('template', 'default'),
            ('custom_body_cl_default', ''),
            ('custom_box_bottom_cl_default', ''),
            ('custom_box_bottom_default', ''),
            ('custom_box_top_cl_default', ''),
            ('custom_box_top_default', ''),
            ('custom_box_left_cl_default', ''),
            ('custom_box_left_default', ''),
            ('custom_box_right_cl_default', ''),
            ('custom_box_right_default', ''),
            ('custom_css_default', '/* custom_css */\\n/* center everything! */\\n.row {\\n    text-align: center;\\n}\\n#recaptcha_widget_div, #recaptcha_area {\\n    margin: 0 auto;\\n}\\n/* do not center lists */\\nul, ol {\\n    text-align: left;\\n}'),
            ('custom_footer_cl_default', ''),
            ('custom_footer_default', ''),
            ('custom_main_box_cl_default', ''),
            ('custom_palette_default', ''),
            ('version', '1'),
            ('currency', 'BTC')
        "
    ),
    15 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('version', '15');"),
    17 => array("ALTER TABLE `" . $dbtable_prefix . "Settings` CHANGE `value` `value` TEXT NOT NULL;", "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('balance', 'N/A');"),
    33 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('ayah_publisher_key', ''), ('ayah_scoring_key', '');"),
    34 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('custom_admin_link_default', 'true')"),
    38 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('reverse_proxy', 'none')", "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('default_captcha', 'recaptcha')"),
    41 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('captchme_public_key', ''), ('captchme_private_key', ''), ('captchme_authentication_key', ''), ('reklamper_enabled', '')"),
    46 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('last_balance_check', '0')"),
    55 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('block_adblock', ''), ('button_timer', '0')"),
    56 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('ip_check_server', ''),('ip_ban_list', ''),('hostname_ban_list', ''),('address_ban_list', '')"),
    58 => array("DELETE FROM `" . $dbtable_prefix . "Settings` WHERE `name` IN ('captchme_public_key', 'captchme_private_key', 'captchme_authentication_key', 'reklamper_enabled')"),
    63 => array("INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('safety_limits_end_time', '')"),
    64 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('iframe_sameorigin_only', ''), ('asn_ban_list', ''), ('country_ban_list', ''), ('nastyhosts_enabled', '')",
        "UPDATE `" . $dbtable_prefix . "Settings` new LEFT JOIN `" . $dbtable_prefix . "Settings` old ON old.name = 'ip_check_server' SET new.value = IF(old.value = 'http://v1.nastyhosts.com/', 'on', '') WHERE new.name = 'nastyhosts_enabled'",
        "DELETE FROM `" . $dbtable_prefix . "Settings` WHERE `name` = 'ip_check_server'"
    ),
    65 => array(
        "DELETE FROM `" . $dbtable_prefix . "Settings` WHERE `name` IN ('ayah_publisher_key', 'ayah_scoring_key') ",
        "UPDATE `" . $dbtable_prefix . "Settings` SET `value` = IF(`value` != 'none' OR `value` != 'none-auto', 'on', '') WHERE `name` = 'reverse_proxy' "
    ),
    66 => array(
        "ALTER TABLE `" . $dbtable_prefix . "Settings` CHANGE `value` `value` LONGTEXT NOT NULL;",
        "CREATE TABLE IF NOT EXISTS `" . $dbtable_prefix . "IP_Locks` ( `ip` VARCHAR(45) NOT NULL PRIMARY KEY, `locked_since` TIMESTAMP NOT NULL );",
        "CREATE TABLE IF NOT EXISTS `" . $dbtable_prefix . "Address_Locks` ( `address` VARCHAR(110) NOT NULL PRIMARY KEY, `locked_since` TIMESTAMP NOT NULL );"
    ),
    67 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('ip_white_list', ''), ('update_last_check', '');"
    ),
    80 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('disable_refcheck', '');",
        "CREATE TABLE if not exists `" . $dbtable_prefix . "NH_Log` (
          `" . $dbtable_prefix . "NH_Log_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `" . $dbtable_prefix . "NH_Log_time` int(11) NOT NULL DEFAULT '0',
          `" . $dbtable_prefix . "NH_Log_IP` varchar(45) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_address` varchar(110) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_address_ref` varchar(110) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_suggestion` enum('allow','deny') NOT NULL DEFAULT 'deny',
          `" . $dbtable_prefix . "NH_Log_reason` varchar(128) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_country_code` varchar(3) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_country` varchar(64) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_asn` int(11) NOT NULL DEFAULT '0',
          `" . $dbtable_prefix . "NH_Log_asn_name` varchar(128) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_host` varchar(128) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_useragent` varchar(256) NOT NULL DEFAULT '',
          `" . $dbtable_prefix . "NH_Log_session_id` varchar(50) NOT NULL DEFAULT '',
          PRIMARY KEY (`" . $dbtable_prefix . "NH_Log_id`),
          KEY `" . $dbtable_prefix . "NH_Log_time` (`" . $dbtable_prefix . "NH_Log_time`),
          KEY `" . $dbtable_prefix . "NH_Log_session_id` (`" . $dbtable_prefix . "NH_Log_session_id`)
        );"
    ),
    85 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('ezdata', 'on'), ('shortlink_payout', '0'), ('shortlink_data', ''), ('update_data', '');"
    ),
    87 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('shortlink_required', ''), ('custom_admin_link_SpaceRacket', 'true'), ('custom_admin_link_base', 'true'), ('limit_number_of_claims_per_24h', '0'), ('raincaptcha_public_key', ''), ('raincaptcha_secret_key', ''), ('show_recent_payouts', 'on'), ('show_referred_users', 'on');",
        "CREATE TABLE if not exists `" . $dbtable_prefix . "Claimlog` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `address` varchar(110) NOT NULL DEFAULT '',
          `ip` varchar(45) NOT NULL DEFAULT '',
          `time` int(11) NOT NULL DEFAULT 0,
          `shortlink` varchar(32) NOT NULL DEFAULT '',
          `reward` varchar(16) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `address_ip_time_shortink` (`address`,`ip`,`time`,`shortlink`)
        );"
    ),
    88 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('iphub_enabled', ''), ('iphub_api_key', '');"
    ),
    90 => array(
        "ALTER TABLE `" . $dbtable_prefix . "ABL_Log` CHANGE `" . $dbtable_prefix . "ABL_Log_address` `" . $dbtable_prefix . "ABL_Log_address` varchar(110) NOT NULL DEFAULT '' AFTER `" . $dbtable_prefix . "ABL_Log_IP`, CHANGE `" . $dbtable_prefix . "ABL_Log_address_ref` `" . $dbtable_prefix . "ABL_Log_address_ref` varchar(110) NOT NULL DEFAULT '' AFTER `" . $dbtable_prefix . "ABL_Log_address`;",
        "ALTER TABLE `" . $dbtable_prefix . "Addresses` CHANGE `address` `address` varchar(110) NOT NULL FIRST, CHANGE `last_used` `last_used` timestamp NOT NULL DEFAULT current_timestamp() AFTER `ref_id`;",
        "ALTER TABLE `" . $dbtable_prefix . "Address_Locks` CHANGE `address` `address` varchar(110) NOT NULL FIRST, CHANGE `locked_since` `locked_since` timestamp NOT NULL DEFAULT current_timestamp() AFTER `address`;",
        "ALTER TABLE `" . $dbtable_prefix . "Claimlog` CHANGE `address` `address` varchar(110) NOT NULL DEFAULT '' AFTER `id`;",
        "ALTER TABLE `" . $dbtable_prefix . "NH_Log` CHANGE `" . $dbtable_prefix . "NH_Log_address` `" . $dbtable_prefix . "NH_Log_address` varchar(110) NOT NULL DEFAULT '' AFTER `" . $dbtable_prefix . "NH_Log_IP`, CHANGE `" . $dbtable_prefix . "NH_Log_address_ref` `" . $dbtable_prefix . "NH_Log_address_ref` varchar(110) NOT NULL DEFAULT '' AFTER `" . $dbtable_prefix . "NH_Log_address`;",
        "ALTER TABLE `" . $dbtable_prefix . "Refs` CHANGE `address` `address` varchar(110) COLLATE 'utf8mb4_unicode_ci' NOT NULL AFTER `id`;",
    ),
    93 => array(
        "CREATE TABLE if not exists `" . $dbtable_prefix . "Shortlinks` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `shortlink` varchar(32) NOT NULL DEFAULT '',
          `link` varchar(250) NOT NULL DEFAULT '',
          `time` int(11) NOT NULL DEFAULT 0,
          `hash` varchar(32) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`),
          KEY `shortlink_time` (`shortlink`,`time`)
        );",
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('reward_in_USD', ''), ('reward_in_USD_last_check', '0'), ('reward_in_USD_rate', '0'), ('hcaptcha_site_key', ''), ('hcaptcha_secret_key', '');"
    ),
    95 => array(
        "TRUNCATE TABLE `" . $dbtable_prefix . "Shortlinks`;"
    ),
    96 => array(
        "CREATE TABLE if not exists `" . $dbtable_prefix . "ProxyCheck` (
          `ip` varchar(45) NOT NULL DEFAULT '',
          `time` int(11) NOT NULL DEFAULT 0,
          `data` mediumtext NOT NULL,
          PRIMARY KEY (`ip`)
        );",
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('proxycheck_enabled', ''), ('proxycheck_api_key', ''), ('proxycheck_priority', '100'), ('nastyhosts_priority', '200'), ('iphub_priority', '300');"
    ),
    97 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('disallow_www', 'on');"
    ),
    101 => array(
        "ALTER TABLE `" . $dbtable_prefix . "Shortlinks` ADD `users_sent` int(11) NOT NULL DEFAULT '0', ADD `users_returned` int(11) NOT NULL DEFAULT '0' AFTER `users_sent`;",
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('usertoken', '');"
    ),
    102 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('kswallet_meta_id', '');"
    ),
    103 => array(
        "ALTER TABLE `" . $dbtable_prefix . "IP_Locks` CHANGE `ip` `ip` varchar(45);"
    ),
    104 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('service', 'faucetpay');"
    ),
    107 => array(
        "INSERT IGNORE INTO `" . $dbtable_prefix . "Settings` (`name`, `value`) VALUES ('enable_admin_captcha', ''), ('code_2fa', ''), ('disallow_email_claiming', 'on'), ('enable_metrix', 'on');"
    ),
    118 => array(
        "ALTER TABLE `" . $dbtable_prefix . "Addresses` ADD COLUMN IF NOT EXISTS `first_used` timestamp NOT NULL DEFAULT current_timestamp();",
        "ALTER TABLE `" . $dbtable_prefix . "Addresses` ADD COLUMN IF NOT EXISTS `user_hash` varchar(64) NOT NULL DEFAULT '';",
        "ALTER TABLE `" . $dbtable_prefix . "Addresses` ADD INDEX `user_hash` (`user_hash`);"
    )
);

$initial_install = false;
if (!isset($faucet_settings_array['version'])) {
    $dbversion = -1;
    $initial_install = true;
} else {
    $dbversion = $faucet_settings_array['version'];
}

foreach ($db_updates as $v => $update) {
    if ($v > $dbversion) {
        foreach ($update as $query) {
            $sql->exec($query);
            usleep(2500);
        }
    }
}

if (intval($version) > intval($dbversion)) {
    $q = $sql->prepare("UPDATE `" . $dbtable_prefix . "Settings` SET `value` = ? WHERE `name` = 'version'");
    $q->execute(array($version));
    $q = $sql->prepare("UPDATE `" . $dbtable_prefix . "Settings` SET `value` = ? WHERE `name` = 'update_last_check'");
    $q->execute(array('0'));
    // reload settings
    $faucet_settings_array = fb_load_settings();
    if ($initial_install) {
        // set password
        require_once("script/admin_templates.php");
        $password = setNewPass();
        $page = str_replace('<:: content ::>', $pass_template, $master_template);
        $page = str_replace('<:: password ::>', $password, $page);
        die($page);
    }
}
