<?php

// Admin Log 1.06

class adminlog {
    var $settings = array();

    public function __construct($connection_options = null) {
        global $sql, $faucet_settings_array, $dbtable_prefix, $session_prefix;
        // check if adminlog already installed
        foreach ($faucet_settings_array as $faucet_settings_name => $faucet_settings_value) {
            $set_pos = strpos($faucet_settings_name, 'adminlog_');
            if (($set_pos !== false) && ($set_pos == 0)) {
                $this->settings[$faucet_settings_name] = $faucet_settings_value;
            }
        }
        //
        if (count($this->settings) == 0) {
            $sql->exec("INSERT INTO " . $dbtable_prefix . "Settings SET `name`='adminlog_enabled', `value`='on';");
            $settings_array = $sql->query("SELECT `name`, `value` FROM `" . $dbtable_prefix . "Settings` WHERE `name` LIKE 'adminlog_%';")->fetchAll();
            foreach ($settings_array as $k => $v) {
                $this->settings[$v['name']] = $v['value'];
            }
        }
        //
        if (!empty($_POST)) {
            if ((!empty($_SESSION['address_input_name' . $session_prefix])) && (!empty($_POST[$_SESSION['address_input_name' . $session_prefix]]))) {
                $q = $sql->prepare("INSERT INTO " . $dbtable_prefix . "Sessions_Log SET " . $dbtable_prefix . "Sessions_Log_time=?, " . $dbtable_prefix . "Sessions_Log_session_id=?, " . $dbtable_prefix . "Sessions_Log_message=?;");
                $q->execute(array(time(), session_id() . '-' . getUniqueRequestID(), '<a href="#" class="address">' . htmlspecialchars($_POST[$_SESSION['address_input_name' . $session_prefix]]) . '</a>'));
                // Delete the log that is older than a day - for better performance execute every ~20 requests
                if (mt_rand(0, 20) == 5) {
                    $sql->exec("DELETE FROM `" . $dbtable_prefix . "Sessions_Log` WHERE " . $dbtable_prefix . "Sessions_Log_time<" . (time() - 86400) . ";");
                }
            }
        }
    }

    public function admin_set_message($msg) {
        global $sql, $dbtable_prefix;
        $msg = substr($msg, 0, 1024);
        $q = $sql->prepare("UPDATE " . $dbtable_prefix . "Sessions_Log SET " . $dbtable_prefix . "Sessions_Log_message=? WHERE " . $dbtable_prefix . "Sessions_Log_session_id=?;");
        $q->execute(array($msg, session_id() . '-' . getUniqueRequestID()));
    }

    public function admin_config_top() {
        global $sql, $dbtable_prefix, $session_prefix;
        if (isset($_SESSION['logged_in' . $session_prefix])) {
            if (!empty($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'ajax_admin_get_log':
                        if (empty($_POST['last_id'])) {
                            $last_id = 0;
                        } else {
                            $last_id = (int)$_POST['last_id'];
                        }
                        //
                        $adminlog_log_response = array();
                        $adminlog_log_response['log'] = array();
                        $maxid = $last_id;

                        // check installed tables
                        $adminlog_exist = $sql->query("SELECT 1 FROM " . $dbtable_prefix . "NH_Log LIMIT 1;")->fetch();
                        $abl_exist = $sql->query("SELECT 1 FROM " . $dbtable_prefix . "ABL_Log LIMIT 1;")->fetch();

                        // select installed tables
                        $adminlog_query_raw_select = "SELECT sl." . $dbtable_prefix . "Sessions_Log_time, sl." . $dbtable_prefix . "Sessions_Log_session_id, sl." . $dbtable_prefix . "Sessions_Log_message ";
                        $adminlog_query_raw_from = "FROM " . $dbtable_prefix . "Sessions_Log sl ";
                        $adminlog_query_raw_where = "WHERE " . $dbtable_prefix . "Sessions_Log_time>" . (time() - 86400) . " AND " . $dbtable_prefix . "Sessions_Log_time<" . (time() - 5) . " AND " . $dbtable_prefix . "Sessions_Log_time>" . (int)$maxid . " ";
                        $adminlog_query_raw_order = "GROUP BY sl." . $dbtable_prefix . "Sessions_Log_session_id ORDER BY " . $dbtable_prefix . "Sessions_Log_time DESC LIMIT 2500;";

                        if ($adminlog_exist) {
                            $adminlog_query_raw_select .= ", nhl.* ";
                            $adminlog_query_raw_from .= "LEFT JOIN " . $dbtable_prefix . "NH_Log nhl ON (sl." . $dbtable_prefix . "Sessions_Log_session_id=nhl." . $dbtable_prefix . "NH_Log_session_id) ";
                        }
                        if ($abl_exist) {
                            $adminlog_query_raw_select .= ", abl.* ";
                            $adminlog_query_raw_from .= "LEFT JOIN " . $dbtable_prefix . "ABL_Log abl ON (sl." . $dbtable_prefix . "Sessions_Log_session_id=abl." . $dbtable_prefix . "ABL_Log_session_id) ";
                        }
                        // merge all of them
                        $adminlog_query_raw = $adminlog_query_raw_select . $adminlog_query_raw_from . $adminlog_query_raw_where . $adminlog_query_raw_order;

                        // exec the query
                        $adminlog_query = $sql->query($adminlog_query_raw);
                        while ($adminlog_row = $adminlog_query->fetch()) {
                            if ($adminlog_row[$dbtable_prefix . 'Sessions_Log_time'] > $maxid) {
                                $maxid = $adminlog_row[$dbtable_prefix . 'Sessions_Log_time'];
                            }
                            $adminlog = array();
                            $adminlog['Sessions_Log_time'] = '<b>' . date('Y.m.d', $adminlog_row[$dbtable_prefix . 'Sessions_Log_time']) . '</b><br />' . date('H:i:s', $adminlog_row[$dbtable_prefix . 'Sessions_Log_time']);
                            // IP
                            $adminlog['ip'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_IP'])) {
                                $adminlog['ip'] = $adminlog_row[$dbtable_prefix . 'NH_Log_IP'];
                            }
                            if (!empty($adminlog_row[$dbtable_prefix . 'ABL_Log_IP'])) {
                                $adminlog['ip'] = $adminlog_row[$dbtable_prefix . 'ABL_Log_IP'];
                            }
                            // Message
                            $adminlog['message'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'Sessions_Log_message'])) {
                                $adminlog['message'] = nl2br($adminlog_row[$dbtable_prefix . 'Sessions_Log_message']);
                            }
                            // UserAgent
                            $adminlog['useragent'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_useragent'])) {
                                $adminlog['useragent'] = $adminlog_row[$dbtable_prefix . 'NH_Log_useragent'];
                            }
                            // Country
                            $adminlog['country'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_country'])) {
                                $adminlog['country'] = $adminlog_row[$dbtable_prefix . 'NH_Log_country'];
                            }
                            // Country Code
                            $adminlog['country_code'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_country_code'])) {
                                $adminlog['country_code'] = $adminlog_row[$dbtable_prefix . 'NH_Log_country_code'];
                            }
                            // host
                            $adminlog['host'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_host'])) {
                                $adminlog['host'] = $adminlog_row[$dbtable_prefix . 'NH_Log_host'];
                            }
                            // asn
                            $adminlog['asn'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_asn'])) {
                                $adminlog['asn'] = $adminlog_row[$dbtable_prefix . 'NH_Log_asn'];
                            }
                            // asn_name
                            $adminlog['asn_name'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_asn_name'])) {
                                $adminlog['asn_name'] = $adminlog_row[$dbtable_prefix . 'NH_Log_asn_name'];
                            }
                            // address
                            $adminlog['address'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_address'])) {
                                $adminlog['address'] = htmlspecialchars($adminlog_row[$dbtable_prefix . 'NH_Log_address']);
                            }
                            if (!empty($adminlog_row[$dbtable_prefix . 'ABL_Log_address'])) {
                                $adminlog['address'] = htmlspecialchars($adminlog_row[$dbtable_prefix . 'ABL_Log_address']);
                            }
                            // address ref
                            $adminlog['address_ref'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_address_ref'])) {
                                $adminlog['address_ref'] = htmlspecialchars($adminlog_row[$dbtable_prefix . 'NH_Log_address_ref']);
                            }
                            if (!empty($adminlog_row[$dbtable_prefix . 'ABL_Log_address_ref'])) {
                                $adminlog['address_ref'] = htmlspecialchars($adminlog_row[$dbtable_prefix . 'ABL_Log_address_ref']);
                            }
                            //
                            $adminlog['ABL_Log_status'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'ABL_Log_status'])) {
                                $adminlog['ABL_Log_status'] = $adminlog_row[$dbtable_prefix . 'ABL_Log_status'];
                            }
                            if ($abl_exist) {
                                if (empty($adminlog['ABL_Log_status'])) {
                                    $adminlog['ABL_Log_status'] = '?';
                                }
                            }
                            $adminlog['NH_Log_reason'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_reason'])) {
                                $adminlog['NH_Log_reason'] = $adminlog_row[$dbtable_prefix . 'NH_Log_reason'];
                            }
                            $adminlog['NH_Log_suggestion'] = '';
                            if (!empty($adminlog_row[$dbtable_prefix . 'NH_Log_suggestion'])) {
                                $adminlog['NH_Log_suggestion'] = $adminlog_row[$dbtable_prefix . 'NH_Log_suggestion'];
                            }
                            if ($adminlog_exist) {
                                if (empty($adminlog['NH_Log_reason'])) {
                                    $adminlog['NH_Log_reason'] = '?';
                                }
                                if (empty($adminlog['NH_Log_suggestion'])) {
                                    $adminlog['NH_Log_suggestion'] = '?';
                                }
                            }
                            //
                            $adminlog_log_response['log'][] = $adminlog;
                        }
                        // reverse the array
                        $adminlog_log_response['log'] = array_reverse($adminlog_log_response['log']);

                        $adminlog_log_response['last_id'] = $maxid;
                        echo json_encode($adminlog_log_response);
                        exit;
                        break;
                }
            }
        }
    }

    public function admin_config() {
        global $sql, $page, $currency, $dbtable_prefix, $session_prefix;

        // check installed tales
        $adminlog_exist = $sql->query("SELECT 1 FROM " . $dbtable_prefix . "NH_Log LIMIT 1;")->fetch();
        $abl_exist = $sql->query("SELECT 1 FROM " . $dbtable_prefix . "ABL_Log LIMIT 1;")->fetch();

        $adminlog_log = '<div id="adminlog_log" style="min-height:200px;">...</div>';
        $adminlog_log .= '
<script tyle="text/javascript">
var adminlog_claim_last_id=0;
var adminlog_claim_data=[];
var adminlog_claim_active=false;

function adminlog_claim_loop() {
    $.post(\'' . basename($_SERVER['PHP_SELF']) . '\', {action:\'ajax_admin_get_log\', last_id:adminlog_claim_last_id, csrftoken:\'' . $_SESSION['csrftoken' . $session_prefix] . '\'})
        .done(function(jsonData) {
            if ((jsonData!=\'\')&&(jsonData.indexOf(\'<!DOCTYPE html>\')==-1)) {
                var data=JSON.parse(jsonData);
                adminlog_claim_last_id=data[\'last_id\'];
                for (var z=0;z<data[\'log\'].length;z++) {
                    adminlog_claim_data[adminlog_claim_data.length]=data[\'log\'][z];
                }
                var data_string=\'\';

                data_string+=\'<table style="border:1px solid #AAAAAA;font-size:10px;width:100%;">\';
                data_string+=\'<tr style="background-color:#EEEEEE;font-weight:bold;">\';
                data_string+=\'<td>Date<br />Time</td>\';';
        if (($abl_exist) || ($adminlog_exist)) {
            $adminlog_log .= '
                data_string+=\'<td>IP<br />Host</td>\';
                data_string+=\'<td>Address<br />REF Address</td>\';';
        }
        if ($abl_exist) {
            $adminlog_log .= '
                    data_string+=\'<td>Suggestion<br />AB<br />Message</td>\';';
        }
        if ($adminlog_exist) {
            $adminlog_log .= '
                    data_string+=\'<td>Suggestion<br />IP Checker<br />Message</td>\';
                    data_string+=\'<td>Country Code<br />Country</td>\';
                    data_string+=\'<td>ASN<br />ASN Name</td>\';';
        }
        $adminlog_log .= '
                data_string+=\'<td>Gateway<br />Response</td>\';
                data_string+=\'</tr>\';';
        /// loop
        $adminlog_log .= '
                for (var z=adminlog_claim_data.length-1;z>=0;z--) {
                    data_string+=\'<tr style="border-top:1px solid #AAAAAA;">\';
                    data_string+=\'<td><b>\'+adminlog_claim_data[z][\'Sessions_Log_time\']+\'</td>\';';
        if (($abl_exist) || ($adminlog_exist)) {
            $adminlog_log .= '
                    data_string+=\'<td title="\'+adminlog_claim_data[z][\'useragent\']+\'"><b><a href="https://makejar.com/ip-details/?ip=\'+adminlog_claim_data[z][\'ip\']+\'" target="_blank" style="color:#5555AA;" title="View details about \'+adminlog_claim_data[z][\'ip\']+\'">\'+adminlog_claim_data[z][\'ip\']+\'</a></b><br />\'+adminlog_claim_data[z][\'host\'];
                    data_string+=\'</td>\';
                    data_string+=\'<td>\';
                    if (adminlog_claim_data[z][\'address\']!=\'\') {
                        data_string+=\'<a href="#" class="address">\'+adminlog_claim_data[z][\'address\']+\'</a>\';
                    }
                    if (adminlog_claim_data[z][\'address_ref\']!=\'\') {
                        data_string+=\'<br /><a href="#" class="address">\'+adminlog_claim_data[z][\'address_ref\']+\'</a>\';
                    }' .
                '
                    data_string+=\'</td>\';
                    ';
        }
        if ($abl_exist) {
            $adminlog_log .= '
                var al_row_css=\'\';
                switch (adminlog_claim_data[z][\'ABL_Log_status\']) {
                    case \'?\':
                    break;
                    case \'valid\':
                        al_row_css=\'background-color:#DDFFDD;\';
                    break;
                    case \'invalid\':
                    case \'bot\':
                    case \'possibly bot\':
                        al_row_css=\'background-color:#FFDDDD;\';
                    break;
                }
                data_string+=\'<td style="\'+al_row_css+\'"><b>\'+adminlog_claim_data[z][\'ABL_Log_status\']+\'</b></td>\';
            ';
        }
        if ($adminlog_exist) {
            $adminlog_log .= '
                var al_row_css=\'\';
                switch (adminlog_claim_data[z][\'NH_Log_suggestion\']) {
                    case \'?\':
                        adminlog_claim_data[z][\'NH_Log_reason\']=\'\';
                    break;
                    case \'allow\':
                        al_row_css=\'background-color:#DDFFDD;\';
                        adminlog_claim_data[z][\'NH_Log_reason\']=\'\';
                    break;
                    case \'deny\':
                        al_row_css=\'background-color:#FFDDDD;\';
                    break;
                }
                data_string+=\'<td style="\'+al_row_css+\'"><b>\'+adminlog_claim_data[z][\'NH_Log_suggestion\']+\'</b><br />\'+adminlog_claim_data[z][\'NH_Log_reason\']+\'</td>\';
            ';
            // loop
            $adminlog_log .= '
                    data_string+=\'<td><b>\'+adminlog_claim_data[z][\'country_code\']+\'</b><br />\'+adminlog_claim_data[z][\'country\']+\'</td>\';
                    if (adminlog_claim_data[z][\'asn\']>0) {
                        data_string+=\'<td><b>\'+adminlog_claim_data[z][\'asn\']+\'</b><br />\'+adminlog_claim_data[z][\'asn_name\']+\'</td>\';
                    } else {
                        data_string+=\'<td></td>\';
                    }
            ';
        }
        // gateway response - message
        $adminlog_log .= '
                data_string+=\'<td>\'+adminlog_claim_data[z][\'message\']+\'</td>\';
        ';
        $adminlog_log .= 'data_string+=\'</tr>\';';
        $adminlog_log .= '}';
        $adminlog_log .= 'data_string+=\'</table>\';';

        $adminlog_log .= '
                $(\'#adminlog_log\').html(data_string);
                $(\'.address\').css(\'font-weight\', \'bold\').on(\'click\', function () {
                    $(\'#referral_address\').val($(this).text());
                    $(\'#check_referral\').click();
                    $(\'.nav-tabs a[href="#referrals"]\').tab(\'show\');
                    return false;
                });
            } else {
                location.reload();
            }
        });
    setTimeout(\'adminlog_claim_loop();\', 30000);
    return false;
}

$(function(){
    $(\'#log\').on(\'mousemove\', function(){
        if (!adminlog_claim_active) {
            adminlog_claim_active=true;
            adminlog_claim_loop();
        }
    });
    $(\'#log_tab\').on(\'click\', function(){
        if (!adminlog_claim_active) {
            adminlog_claim_active=true;
            adminlog_claim_loop();
        }
    });
});
</script>
';

        // show the log
        $page = str_replace('<:: admin_log ::>', $adminlog_log, $page);
    }
}
