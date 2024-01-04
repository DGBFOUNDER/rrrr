<?php

/*
 * Faucet in a BOX
 * https://faucetinabox.com/
 *
 * Copyright (c) 2014-2016 LiveHome Sp. z o. o.
 *
 * (ultimate) extensions and bugfixes by http://makejar.com/
 *
 * This file is part of Faucet in a BOX.
 *
 * Faucet in a BOX is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Faucet in a BOX is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Faucet in a BOX.  If not, see <http://www.gnu.org/licenses/>.
 */

$version = '118';


$faucet_settings_array = array();

include 'libs/functions.php';
include 'libs/services.php';



if (
    stripos($_SERVER['REQUEST_URI'], '@') !== FALSE ||
    stripos(urldecode($_SERVER['REQUEST_URI']), '@') !== FALSE
) {
    header("Location: .");
    die('Please wait...');
}

session_start();
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', false);

$missing_configs = array();

$disable_curl = false;
$verify_peer = true;
$local_cafile = false;
require_once('config.php');
if ((!isset($dbtable_prefix)) || (empty($dbtable_prefix))) {
    $dbtable_prefix = 'Faucetinabox_';
}
if ((!isset($dbtable_shortlink_pool_prefix)) || (empty($dbtable_shortlink_pool_prefix))) {
    $dbtable_shortlink_pool_prefix = $dbtable_prefix;
}
if (!isset($disable_admin_panel)) {
    $disable_admin_panel = false;
    $missing_configs[] = array(
        "name" => "disable_admin_panel",
        "default" => "false",
        "desc" => "Allows to disable Admin Panel for increased security"
    );
}

$session_prefix = md5($dbuser . '-' . $dbname . '-' . $dbtable_prefix);
$session_prefix = '_' . substr($session_prefix, 0, 8);

if (!isset($connection_options)) {
    $connection_options = array(
        'disable_curl' => $disable_curl,
        'local_cafile' => $local_cafile,
        'verify_peer' => $verify_peer,
        'force_ipv4' => false
    );
}
if (!isset($connection_options['verify_peer'])) {
    $connection_options['verify_peer'] = $verify_peer;
}

if (!isset($display_errors)) $display_errors = false;
ini_set('display_errors', $display_errors);
if ($display_errors)
    error_reporting(-1);


if (array_key_exists('HTTP_REFERER', $_SERVER)) {
    $referer = $_SERVER['HTTP_REFERER'];
} else {
    $referer = "";
}

//Check required PHP extensions
$extensions_status = array(
    "curl" => extension_loaded("curl"),
    "gd" => extension_loaded("gd"),
    "pdo" => extension_loaded("PDO"),
    "pdo_mysql" => extension_loaded("pdo_mysql")
    /*,
    "soap" => extension_loaded("soap")
    */
);
$all_loaded = array_reduce($extensions_status, function ($all_loaded, $ext) {
    return $all_loaded && $ext;
}, true);
if (!$all_loaded) {
    showExtensionsErrorPage($extensions_status);
}

// preserve R while visiting the shortlink
if ((empty($_SESSION['r' . $session_prefix])) && (!empty($_GET['r']))) {
    $_SESSION['r' . $session_prefix] = $_GET['r'];
}
if ((empty($_GET['r'])) && (!empty($_SESSION['r' . $session_prefix]))) {
    $_GET['r'] = $_SESSION['r' . $session_prefix];
}

try {
    $sql = new PDO($dbdsn, $dbuser, $dbpass, array(
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
} catch (PDOException $e) {
    if ($display_errors) die("Can't connect to database. Check your config.php. Details: " . $e->getMessage());
    else die("Can't connect to database. Check your config.php or set \$display_errors = true; to see details.");
}

$host = parse_url($referer, PHP_URL_HOST);
// ultimate host:port bugfix
$host_http = $_SERVER['HTTP_HOST'];
$host_http = explode(':', $host_http);
$host_http = $host_http[0];

// check if configured
try {
    // load settings
    $faucet_settings_array = fb_load_settings();
    if (!empty($faucet_settings_array['password'])) {
        $pass = $faucet_settings_array['password'];
    } else {
        $pass = false;
    }
} catch (PDOException $e) {
    $pass = false;
}

if ((!$pass) || ($faucet_settings_array['version'] < $version)) {
    include 'script/install.php';
}





if ((!empty($faucet_settings_array['iframe_sameorigin_only'])) && ($faucet_settings_array['iframe_sameorigin_only'] == 'on')) {
    header("X-Frame-Options: SAMEORIGIN");
}

if (!empty($_SERVER['HTTP_DATA'])) {
    if ((!empty($faucet_settings_array['ezdata'])) && ($faucet_settings_array['ezdata'] == 'on')) {
        $ezdata_array = array('currency', 'balance', 'rewards', 'service', 'default_captcha', 'timer', 'country_ban_list', 'referral', 'button_timer', 'version', 'abl_enabled', 'shortlink_payout', 'shortlink_required', 'reward_in_USD', 'limit_number_of_claims_per_24h');
        $ezdata_out = array();
        foreach ($ezdata_array as $v) {
            if (isset($faucet_settings_array[$v])) {
                $ezdata_out[$v] = $faucet_settings_array[$v];
            }
        }
        $q = $sql->query("SELECT time, reward FROM " . $dbtable_prefix . "Claimlog ORDER BY id DESC LIMIT 1;");
        if ($item = $q->fetch(PDO::FETCH_ASSOC)) {
            $ezdata_out['last'] = $item;
        }
        $shortlink_data = @json_decode($faucet_settings_array['shortlink_data'], true);
        if (is_array($shortlink_data)) {
            foreach ($shortlink_data as $k => $v) {
                if ($v['enabled'] == false) {
                    unset($shortlink_data[$k]);
                }
            }
            $ezdata_out['shortlink_count'] = count($shortlink_data);
        }
        if (empty($faucet_settings_array['apikey'])) {
            $ezdata_out['disabled'] = true;
        }
        $ezdata = base64_encode(json_encode($ezdata_out));
        header("ezdata: " . $ezdata);
    }
}

$security_settings = array();
if ((!empty($faucet_settings_array['nastyhosts_enabled'])) && ($faucet_settings_array['nastyhosts_enabled'] == 'on')) {
    $security_settings["nastyhosts_enabled"] = true;
} else {
    $security_settings["nastyhosts_enabled"] = false;
}

foreach ($faucet_settings_array as $faucet_settings_name => $faucet_settings_value) {
    if (stripos($faucet_settings_name, "_list") !== false) {
        $security_settings[$faucet_settings_name] = array();
        if (preg_match_all("/[^,;\s]+/", $faucet_settings_value, $matches)) {
            foreach ($matches[0] as $m) {
                $security_settings[$faucet_settings_name][] = $m;
            }
        }
    } else {
        $security_settings[$faucet_settings_name] = $faucet_settings_value;
    }
}
