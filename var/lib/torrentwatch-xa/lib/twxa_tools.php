<?php

global $config_values;

// PHP JSON, PHP cURL, and PHP mbstring support are assumed to be installed

require_once("twxa_config_lib.php");
require_once("twxa_lastRSS.php");
require_once("twxa_atomparser.php");
require_once("class.bdecode.php");
require_once("class.phpmailer.php");
require_once("class.smtp.php"); // keep paired with require_once("class.phpmailer.php")
require_once("twxa_cache.php");
require_once("twxa_torrent.php");
require_once("twxa_parse.php");
require_once("twxa_feed.php");
require_once("twxa_html.php");

$config_values['Global'] = []; // initialize collection of global arrays

function getArrayValueByKey($array, $key, $default = '') {
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

function getcURLDefaults(&$curlOptions) {
    global $twxa_version;
    $curlOptions[CURLOPT_CONNECTTIMEOUT] = 20; // was 15
    $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
    $curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
    $curlOptions[CURLOPT_FOLLOWLOCATION] = true;
    $curlOptions[CURLOPT_UNRESTRICTED_AUTH] = true;
    $curlOptions[CURLOPT_TIMEOUT] = 30; // was 20
    $curlOptions[CURLOPT_RETURNTRANSFER] = true;
    if (filter_input(INPUT_SERVER, "HTTP_USER_AGENT") == "") {
        $curlOptions[CURLOPT_USERAGENT] = "torrentwatch-xa/$twxa_version[0] ($twxa_version[1])";
        //$curlOptions[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
    } else {
        $curlOptions[CURLOPT_USERAGENT] = filter_input(INPUT_SERVER, "HTTP_USER_AGENT");
    }
    return($curlOptions);
}

function getCurrentMicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

function getElapsedMicrotime($startTime) {
    return (getCurrentMicrotime() - $startTime);
}

function sanitizeFilename($filename) {
    // makes a filename filesystem-safe
    return preg_replace("/\?|\/|\\|\+|\=|\>|\<|\,|\"|\*|\|/", "_", $filename);
}

function writeToLog($string, $lvl = -1) {
    global $config_values;
    switch ($lvl) {
        case -1: // ALERT:
            $errLabel = "ALR:";
            break;
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
    if (!isset($config_values['Settings']['Log Level']) || $config_values['Settings']['Log Level'] + 0 >= $lvl) {
        /* if ($lvl === -1 && isset($config_values['Global']['HTMLOutput'])) {
          $string = trim(strtr($string, array("'" => "\\'")));
          $debug_output = "<script type='text/javascript'>alert('$string');</script>"; //TODO append errors to some global that will be echoed to the HTML output buffer just once
          } */
        // write plain text to log file
        if (file_put_contents(get_logFile(), date("c") . " $errLabel $string", FILE_APPEND) === false) {
            //TODO failed to write, send error to HTML
        }
    }
}

function notifyByEmail($body, $subject) {
    global $config_values;

    $fromName = $config_values['Settings']['From Name'];
    $fromEmail = $config_values['Settings']['From Email'];
    $toEmail = $config_values['Settings']['To Email'];
    $smtpServer = $config_values['Settings']['SMTP Server'];
    $smtpPort = $config_values['Settings']['SMTP Port'];
    $smtpAuthentication = $config_values['Settings']['SMTP Authentication'];
    $smtpEncryption = $config_values['Settings']['SMTP Encryption'];
    $smtpUser = $config_values['Settings']['SMTP User'];
    $smtpPassword = $config_values['Settings']['SMTP Password'];
    $hELOOverride = $config_values['Settings']['HELO Override'];

    $output = sendEmail($fromName, $fromEmail, $toEmail, $smtpServer, $smtpPort, $smtpAuthentication, $smtpEncryption, $smtpUser, $smtpPassword, $hELOOverride, $subject, $body);
    writeToLog($output['message'], $output['rc'], 2);
}

function sendEmail($fromName, $fromEmail, $toEmail, $smtpServer, $smtpPort, $smtpAuthentication, $smtpEncryption, $smtpUser, $smtpPassword, $hELOOverride, $subject, $body) {
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
            if (filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                $email->From = $fromEmail;
                if ($fromName != '') {
                    $email->FromName = $fromName;
                }
                if ($hELOOverride != '' && filter_var($hELOOverride, FILTER_VALIDATE_DOMAIN)) {
                    $email->Hostname = ''; // sets empty hostname to force setting of HELO to work
                    $email->Helo = $hELOOverride;
                }
                $email->AddAddress($toEmail);
                $email->Host = $smtpServer;
                $email->Port = $smtpPort;
                if ($smtpAuthentication !== 'None') {
                    $email->SMTPAuth = true;
                    $email->AuthType = $smtpAuthentication;
                    $email->Username = $smtpUser;
                    $email->Password = decryptsMTPPassword($smtpPassword);

                    switch ($smtpEncryption) {
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
                $email->Body = $body;
                if (!$email->Send()) {
                    return [
                        'rc' => 0,
                        'message' => "PHPMailer error: " . $email->ErrorInfo
                    ];
                } else {
                    return [
                        'rc' => 1,
                        'message' => "Email sent."
                    ];
                }
            } else {
                return [
                    'rc' => -1,
                    'message' => "Invalid From: Email."
                ];
            }
        } else {
            return [
                'rc' => -1,
                'message' => "Invalid To: Email."
            ];
        }
    } else {
        return [
            'rc' => -1,
            'message' => "Invalid SMTP Port."
        ];
    }
}

function runScript($param, $title, $errorMessage = "") {
    global $config_values;
    $subject = $msg = "";
    $script = trim($config_values['Settings']['Script']);
    if ($script) {
        if (preg_match("/\s+/", $script)) {
            $subject = "Parameters not allowed in Trigger Script: $script";
            $msg = "torrentwatch-xa does not allow parameters in Configure > Trigger > Script: $script";
            writeToLog("Parameters not allowed in Trigger Script: $script\n", 0);
        } else {
            if (is_file($script)) {
                $escapedTitle = escapeshellarg($title);
                $escapedErrorMessage = escapeshellarg($errorMessage);
                writeToLog("Running script: $script $param $escapedTitle $escapedErrorMessage\n", 1);
                $response = [];
                $return = 0;
                exec("$script $param $escapedTitle $escapedErrorMessage 2>&1", $response, $return);
                if ($return) {
                    $responseMsg = implode("\n", $response);
                    $debugMsg = $subject = "Error in Trigger Script: $script";
                    $msg = "torrentwatch-xa encountered an error running:\n$script $param $escapedTitle $escapedErrorMessage\n";
                    if ($responseMsg) {
                        $msg .= "\n" . $responseMsg . "\n\n";
                        $debugMsg .= " $param $escapedTitle $escapedErrorMessage: " . $responseMsg;
                    }
                    $msg .= "Please examine the example scripts in " . get_baseDir() . "/examples for more info about how to make a compatible script.";
                    writeToLog("$debugMsg\n", 0);
                } else {
                    writeToLog("Success running: $script\n", 2);
                }
            } else {
                $subject = "Trigger Script not found: $script";
                $msg = "torrentwatch-xa was unable to find Trigger Script: $script";
                writeToLog("Trigger Script not found: $script\n", 0);
            }
        }
    } else {
        $subject = "Trigger Script not specified";
        $msg = "torrentwatch-xa: Configure > Trigger > Script not specified";
        writeToLog("Trigger Script not specified\n", 0);
    }
    if ($subject && $config_values['Settings']['SMTP Notifications']) {
        notifyByEmail($msg, $subject);
    }
}

function parseURLForCookies($url) {
    $cookies = stristr($url, ':COOKIE:');
    if ($cookies !== false) {
        $url = rtrim(substr($url, 0, -strlen($cookies)), '&');
        $cookies = strtr(substr($cookies, 8), '&', ';');
        return array('url' => $url, 'cookies' => $cookies);
    }
}
