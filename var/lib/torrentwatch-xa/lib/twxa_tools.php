<?php

define('TWXA_VERSION', '1.12.1');

global $config_values;

// Prerequisite PHP JSON, PHP cURL, PHP XML, and PHP mbstring packages are assumed to be installed.

require_once("twxa_config_lib.php");
require_once("twxa_feed_parser_wrapper.php");
require_once("class.bdecode.php");
require_once("class.phpmailer.php");
require_once("class.smtp.php"); // keep paired with require_once("class.phpmailer.php")
require_once("twxa_cache.php");
require_once("twxa_torrent.php");
require_once("twxa_parse.php");
require_once("twxa_feed.php");
require_once("twxa_html.php");

function outputError($message) {
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, "ERROR: $message\n");
    } else {
        echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</div>";
    }
}

function getArrayValueByKey($array, $key, $default = '') {
    // checks array: if a key is set, return value or default
    return isset($array[$key]) ? $array[$key] : $default;
}

function multi_str_search($haystack, $needles) {
    $needlesArray = explode(" ", $needles);
    foreach ($needlesArray as $needle) {
        if ($needle !== '' && strpos($haystack, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function fillCurlOptions($curlOptions = null) {
    // fills out missing curl options for PHP curl
    // existing options get precedence
    (isset($curlOptions[CURLOPT_CONNECTTIMEOUT]) && is_integer($curlOptions[CURLOPT_CONNECTTIMEOUT])) ?: $curlOptions[CURLOPT_CONNECTTIMEOUT] = 20;
    (isset($curlOptions[CURLOPT_SSL_VERIFYPEER]) && is_bool($curlOptions[CURLOPT_SSL_VERIFYPEER])) ?: $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
    (isset($curlOptions[CURLOPT_SSL_VERIFYHOST]) && is_bool($curlOptions[CURLOPT_SSL_VERIFYHOST])) ?: $curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
    (isset($curlOptions[CURLOPT_FOLLOWLOCATION]) && is_bool($curlOptions[CURLOPT_FOLLOWLOCATION])) ?: $curlOptions[CURLOPT_FOLLOWLOCATION] = true;
    (isset($curlOptions[CURLOPT_UNRESTRICTED_AUTH]) && is_bool($curlOptions[CURLOPT_UNRESTRICTED_AUTH])) ?: $curlOptions[CURLOPT_UNRESTRICTED_AUTH] = true;
    (isset($curlOptions[CURLOPT_TIMEOUT]) && is_integer($curlOptions[CURLOPT_TIMEOUT])) ?: $curlOptions[CURLOPT_TIMEOUT] = 30;
    (isset($curlOptions[CURLOPT_RETURNTRANSFER]) && is_bool($curlOptions[CURLOPT_RETURNTRANSFER])) ?: $curlOptions[CURLOPT_RETURNTRANSFER] = true;
    if (isset($curlOptions[CURLOPT_USERAGENT]) && !empty($curlOptions[CURLOPT_USERAGENT])) {
        // do nothing
    } else {
        $userAgent = filter_input(INPUT_SERVER, "HTTP_USER_AGENT");
        if ($userAgent === false || $userAgent === null || $userAgent === '') {
            $curlOptions[CURLOPT_USERAGENT] = "torrentwatch-xa/" . TWXA_VERSION;
        } else {
            $curlOptions[CURLOPT_USERAGENT] = $userAgent;
        }
    }
    return($curlOptions);
}

function getCurl($url, $curlOptions = null, $skipDefaults = false, &$curlErrno = null) {
    // use PHP curl to request the resource at $url and return the contents
    // overwrite the URL with $url if $url is set
    if (isset($url) && is_string($url)) {
        $curlOptions[CURLOPT_URL] = $url;
    }
    $result = false;
    if (isset($curlOptions[CURLOPT_URL]) && is_string($curlOptions[CURLOPT_URL]) && $curlOptions[CURLOPT_URL] !== '') {
        $curlHandle = curl_init();
        if ($curlHandle === false) {
            writeToLog("Unable to init curl handle\n", -1);
        } else {
            if ($skipDefaults) {
                $setOptions = $curlOptions;
            } else {
                $setOptions = fillCurlOptions($curlOptions);
            }
            if (curl_setopt_array($curlHandle, $setOptions)) {
                $response = curl_exec($curlHandle);
                $curlError = curl_error($curlHandle);
                $curlErrno = curl_errno($curlHandle);
                if (PHP_VERSION_ID < 80000) {
                    curl_close($curlHandle);
                }
                if ($response === false) {
                    writeToLog("curl got no response from " . $curlOptions[CURLOPT_URL] . " with error: " . $curlError . "\n", -1);
                } else if ($response === true) {
                    $result = '';
                } else {
                    $result = $response;
                }
            } else {
                writeToLog("Unable to set curl options for " . $curlOptions[CURLOPT_URL] . ": option keys " . implode(", ", array_keys($setOptions)) . "\n", 0);
            }
        }
    } else {
        // no URL provided
        writeToLog("curl cannot request a blank URL\n", 0);
    }
    return $result;
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
    return preg_replace("/\:|\?|\/|\\|\+|\=|\>|\<|\,|\"|\*|\||\[|\]|\^/", "_", $filename);
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
    if (!isset($config_values['Settings']['Log Level']) || (int) $config_values['Settings']['Log Level'] >= $lvl) {
        // write plain text to log file
        if (file_put_contents(get_logFile(), date("c") . " $errLabel $string", FILE_APPEND | LOCK_EX) === false) {
            outputError("Failed to write to log file: " . get_logFile());
        }
    }
}

function notifyByEmail($body, $subject) {
    global $config_values;

    $settings = (isset($config_values['Settings']) && is_array($config_values['Settings'])) ? $config_values['Settings'] : [];
    $smtpAuthentication = isset($settings['SMTP Authentication']) ? $settings['SMTP Authentication'] : '';
    $requiredSettings = [
        'From Email',
        'To Email',
        'SMTP Server',
        'SMTP Authentication'
    ];
    if ($smtpAuthentication !== '' && $smtpAuthentication !== 'None') {
        $requiredSettings[] = 'SMTP User';
        $requiredSettings[] = 'SMTP Password';
    }
    $missingSettings = [];
    foreach ($requiredSettings as $requiredSetting) {
        if (!isset($settings[$requiredSetting])) {
            $missingSettings[] = $requiredSetting;
        }
    }
    if ($missingSettings) {
        writeToLog("Email not sent: config missing required setting(s): " . implode(', ', $missingSettings) . "\n", -1);
        return;
    }

    $fromName = getArrayValueByKey($settings, 'From Name');
    $fromEmail = getArrayValueByKey($settings, 'From Email');
    $toEmail = getArrayValueByKey($settings, 'To Email');
    $smtpServer = getArrayValueByKey($settings, 'SMTP Server');
    $smtpPort = getArrayValueByKey($settings, 'SMTP Port');
    $smtpAuthentication = getArrayValueByKey($settings, 'SMTP Authentication');
    $smtpEncryption = getArrayValueByKey($settings, 'SMTP Encryption');
    $smtpUser = getArrayValueByKey($settings, 'SMTP User');
    $smtpPassword = getArrayValueByKey($settings, 'SMTP Password');
    $hELOOverride = getArrayValueByKey($settings, 'HELO Override');

    $output = sendEmail($fromName, $fromEmail, $toEmail, $smtpServer, $smtpPort, $smtpAuthentication, $smtpEncryption, $smtpUser, $smtpPassword, $hELOOverride, $subject, $body);
    writeToLog($output['message'] . " (rc=" . $output['rc'] . ")", 1);
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
                $hELOIp = $hELOOverride;
                if ($hELOOverride != '') {
                    if (strlen($hELOIp) > 1 && $hELOIp[0] == '[' && substr($hELOIp, -1) == ']') {
                        $hELOIp = substr($hELOIp, 1, -1);
                    }
                    if (
                            filter_var($hELOIp, FILTER_VALIDATE_IP) ||
                            (
                                    strlen($hELOOverride) <= 253 &&
                                    preg_match("/^[A-Za-z0-9_.-]+$/", $hELOOverride) &&
                                    strpos($hELOOverride, "..") === false &&
                                    $hELOOverride[0] != '.'
                            )
                    ) {
                        $email->Hostname = ''; // sets empty hostname to force setting of HELO to work
                        $email->Helo = $hELOOverride;
                    } else {
                        writeToLog("Invalid HELO Override ignored: " . str_replace(["\r", "\n"], " ", $hELOOverride) . " (expected FQDN or IP address)\n", 0);
                    }
                }
                $email->AddAddress($toEmail);
                $email->Host = $smtpServer;
                $email->Port = $smtpPort;
                if ($smtpAuthentication !== 'None') {
                    $email->SMTPAuth = true;
                    $email->AuthType = $smtpAuthentication;
                    $email->Username = $smtpUser;
                    $email->Password = decryptSMTPPassword($smtpPassword);

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
    $script = trim(getArrayValueByKey($config_values['Settings'], 'Script'));
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
    if ($subject && getArrayValueByKey($config_values['Settings'], 'SMTP Notifications')) {
        notifyByEmail($msg, $subject);
    }
}

function parseURLForCookies($url) {
    $cookies = stristr($url, ':COOKIE:');
    if ($cookies !== false) {
        $url = substr($url, 0, -strlen($cookies));
        if (substr($url, -1) === '&') {
            $url = substr($url, 0, -1);
        }
        $cookies = strtr(substr($cookies, 8), '&', ';');
        return [
            'url' => $url,
            'cookies' => $cookies
        ];
    }
}

function chmodPath($path, $perms) {
    if (is_numeric($perms)) {
        if (chmod($path, $perms)) {
            writeToLog("chmod: $path to: $perms succeeded.\n", 1);
            return true;
        } else {
            writeToLog("chmod: $path to: $perms failed.\n", -1);
            return false;
        }
    } else {
        writeToLog("$perms must be numeric (ex.: 0755)\n", -1);
        return false;
    }
}
