<?php

global $config_values;

// PHP JSON, PHP cURL, and PHP mbstring support are assumed to be installed
require_once("/var/lib/torrentwatch-xa/lib/atomparser.php");
require_once("/var/lib/torrentwatch-xa/lib/twxa_cache.php");
require_once("/var/lib/torrentwatch-xa/lib/class.bdecode.php");
require_once("/var/lib/torrentwatch-xa/lib/class.phpmailer.php");
require_once("/var/lib/torrentwatch-xa/lib/class.smtp.php"); // keep paired with require_once("class.phpmailer.php")
require_once("/var/lib/torrentwatch-xa/lib/feeds.php"); // must be before config.php
if (file_exists('/var/lib/torrentwatch-xa/config.php')) { //TODO set to use get_baseDir();
    require_once("/var/lib/torrentwatch-xa/config.php");
}
require_once("/var/lib/torrentwatch-xa/lib/twxa_html.php");
require_once("/var/lib/torrentwatch-xa/lib/lastRSS.php");
require_once("/var/lib/torrentwatch-xa/lib/tor_client.php");
require_once("/var/lib/torrentwatch-xa/lib/twxa_parse.php");

$config_values['Global'] = []; // initialize collection of global arrays

function isset_array_key($array, $key, $default = '') { //TODO rename this
    // checks array: if a key is set, return value or default
    return isset($array[$key]) ? $array[$key] : $default;
}

function multi_str_search($haystack, $needles) {
    $needlesArray = explode(" ", $needles);
    foreach ($needlesArray as $needle) {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }
    }
    return false;
}

// used to lower case all the keys in an array.
// From http://us.php.net/manual/en/function.array-change-key-case.php
define('ARRAY_KEY_FC_LOWERCASE', 25); //FOO => fOO
define('ARRAY_KEY_FC_UPPERCASE', 20); //foo => Foo
define('ARRAY_KEY_UPPERCASE', 15); //foo => FOO
define('ARRAY_KEY_LOWERCASE', 10); //FOO => foo
define('ARRAY_KEY_USE_MULTIBYTE', true); //use mutlibyte functions

/**
 * change the case of array-keys
 *
 * use: array_change_key_case_ext(array('foo' => 1, 'bar' => 2), ARRAY_KEY_UPPERCASE);
 * result: array('FOO' => 1, 'BAR' => 2)
 *
 * @param    array
 * @param    int
 * @return     array
 */
function array_change_key_case_ext($array, $case = ARRAY_KEY_LOWERCASE) {
    $newArray = [];
    //for more speed define the runtime created functions in the global namespace
    //get function
    $function = 'strToUpper'; //default
    switch ($case) {
        //first-char-to-lowercase
        case 25:
            //maybe lcfirst is not callable
            if (!function_exists('lcfirst')) {
                $function = create_function('$input', 'return strToLower($input[0]) . substr($input, 1, (strLen($input) - 1));');
            } else {
                $function = 'lcfirst';
            }
            break;
        //first-char-to-uppercase
        case 20:
            $function = 'ucfirst';
            break;
        //lowercase
        case 10:
            $function = 'strToLower';
    }
    //loop array
    foreach ($array as $key => $value) {
        if (is_array($value)) { //$value is an array, handle keys too
            $newArray[$function($key)] = array_change_key_case_ext($value, $case);
        } elseif (is_string($key)) {
            $newArray[$function($key)] = $value;
        } else {
            $newArray[$key] = $value; //$key is not a string
        }
    } //end loop
    return $newArray;
}

function get_curl_defaults(&$curlopt) {
    if (extension_loaded("curl")) {
        $curlopt[CURLOPT_CONNECTTIMEOUT] = 15;
    }
    $curlopt[CURLOPT_SSL_VERIFYPEER] = false;
    $curlopt[CURLOPT_SSL_VERIFYHOST] = false;
    $curlopt[CURLOPT_FOLLOWLOCATION] = true;
    $curlopt[CURLOPT_UNRESTRICTED_AUTH] = true;
    $curlopt[CURLOPT_TIMEOUT] = 20;
    $curlopt[CURLOPT_RETURNTRANSFER] = true;
    return($curlopt);
}

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

function timer_get_time($time) {
    return (microtime_float() - $time);
}

function filename_encode($filename) {
    // makes a name fit for use as a filename
    return preg_replace("/\?|\/|\\|\+|\=|\>|\<|\,|\"|\*|\|/", "_", $filename);
}

function twxaDebug($string, $lvl = -1) {
    //global $config_values;
    switch ($lvl) {
        case -1: // ALERT:
        case 0:
            $errLabel = "ERR:";
            break;
        case 1:
            $errLabel = "INF:";
            break;
        case 2:
        default:
            $errLabel = "DBG:";
    }
    // write plain text to log file
    file_put_contents(get_logFile(), date("c") . " $errLabel $string", FILE_APPEND);
    /*if ($config_values['Settings']['debugLevel'] >= $lvl) {
        if (isset($config_values['Global']['HTMLOutput'])) {
            // write HTML output
            if ($lvl === -1) {
                $string = trim(strtr($string, array("'" => "\\'")));
                $debug_output = "<script type='text/javascript'>alert('$string');</script>";
            } else {
                $debug_output = date("c") . " $errLabel $string";
            }
            //TODO this block never sends output to HTML!
        } else {
            // write plain text output
            echo(date("c") . " $errLabel $string");
        }
    }*/
}

function MailNotify($msg, $subject) {
    global $config_values;

    $fromEmail = $config_values['Settings']['From Email'];
    $toEmail = $config_values['Settings']['To Email'];
    $smtpPort = $config_values['Settings']['SMTP Port'];

    if (
            (
            $smtpPort != '' &&
            preg_match("/^\d*$/", $smtpPort) &&
            $smtpPort >= 0 &&
            $smtpPort <= 65535
            ) ||
            $smtpPort == '' // if left blank, defaults to 25
    ) {
        // SMTP Port is valid
        if (filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $email = new PHPMailer();
            $email->isSMTP();
            $email->SMTPDebug = 0;

            // set the From: email address
            if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                twxaDebug("From Email is invalid, using To Email as From Email\n", 0);
                $fromEmail = $toEmail;
            }
            $email->From = $fromEmail;
            //$email->FromName = "torrentwatch-xa";
            // prepare the HELO FQDN from the From Email
            $splitEmail = explode('@', $fromEmail);
            $getMX = dns_get_record($splitEmail[1], DNS_MX);
            if (isset($getMX['target'])) {
                $helo = $getMX['target'];
                twxaDebug("Detected HELO from From Email: $helo\n", 2);
            } else if ($splitEmail[1]) {
                $helo = $splitEmail[1];
                twxaDebug("Detected HELO from From Email: $helo\n", 2);
            } else {
                $helo = "localhost.localdomain";
                twxaDebug("Unable to detect HELO from From Email, using default: $helo\n", 2);
            }
            $email->Helo = $helo;

            $email->AddAddress("$toEmail");

            $email->Host = $config_values['Settings']['SMTP Server'];
            $email->Port = $smtpPort;

            if ($config_values['Settings']['SMTP Authentication'] !== 'None') {
                $email->SMTPAuth = true;
                $email->AuthType = $config_values['Settings']['SMTP Authentication'];
                $email->Username = $config_values['Settings']['SMTP User'];
                $email->Password = get_smtp_passwd();

                switch ($config_values['Settings']['SMTP Encryption']) {
                    case 'None':
                        $email->SMTPSecure = '';
                        break;
                    case 'SSL':
                        $email->SMTPSecure = "ssl";
                        break;
                    case 'TLS':
                    default:
                        $email->SMTPSecure = "tls";
                }
            }

            $email->Subject = $subject;

            $mail = file_get_contents("templates/email.tpl"); //TODO use webDir because twxacli.php can't access this
            $mail = str_replace('[MSG]', $msg, $mail);
            if (empty($mail)) {
                $mail = $msg;
            }
            $email->Body = $mail;

            if (!$email->Send()) {
                twxaDebug("Email failed; PHPMailer error: " . print_r($email->ErrorInfo, true) . "\n", 0);
            } else {
                twxaDebug("Mail sent to $toEmail: $subject\n", 1);
            }
        } else {
            // To Email is not valid
            twxaDebug("Email failed: required To Email is not valid\n", -1);
        }
    } else {
        // SMTP Port is not valid
        twxaDebug("Email failed: SMTP Port not valid; leave blank for default of 25 or provide integer from 0-65535\n", -1);
    }
}

function run_script($param, $torrent, $error = "") {
    global $config_values;
    $torrent = escapeshellarg($torrent);
    $error = escapeshellarg($error);
    $script = $config_values['Settings']['Script'];
    if ($script) {
        if (!is_file($script)) {
            if ($config_values['Settings']['SMTP Notifications']) {
                $msg = "The configured script is not a single file. Parameters are not allowed because of security reasons.";
                $subject = "torrentwatch-xa: security error";
                MailNotify($msg, $subject);
            }
            twxaDebug("Notify Script is not a single file; ignoring for security reasons.\n", -1);
            return;
        }
        twxaDebug("Running script: $script $param $torrent $error \n", 1);
        exec("$script $param $torrent $error 2>&1", $response, $return);
        if ($return) {
            $msg = "Something went wrong while running $script:\n";
            foreach ($response as $line) {
                $msg .= $line . "\n";
            }
            $msg.= "\n";
            $msg.= "Please examine the example scripts in /var/lib/torrentwatch-xa/examples for more info about how to make a compatible script.";
            if ($config_values['Settings']['SMTP Notifications']) {
                $subject = "torrentwatch-xa: $script returned error.";
                MailNotify($msg, $subject);
            }
            twxaDebug("$msg\n", 0);
        }
    }
}

function check_for_cookies($url) {
    //if($cookies = stristr($url, ':COOKIE:')) {
    $cookies = stristr($url, ':COOKIE:');
    if ($cookies !== false) {
        $url = rtrim(substr($url, 0, -strlen($cookies)), '&');
        $cookies = strtr(substr($cookies, 8), '&', ';');
        return array('url' => $url, 'cookies' => $cookies);
    }
}

/*function authenticate() {
    global $config_values;

    if ($_SERVER['PHP_AUTH_USER'] == 'twxa' && $_SERVER['PHP_AUTH_PW'] == 'twxa'
    ) {
        $_SESSION['http_logged'] = 1;
    } else {
        $_SESSION['http_logged'] = 0;
        header('WWW-Authenticate: Basic realm="torrentwatch-xa"');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
}*/
