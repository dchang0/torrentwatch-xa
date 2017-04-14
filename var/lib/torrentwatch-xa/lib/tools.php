<?php

function MailNotify($msg, $subject) {
    global $config_values;

    //if ($config_values['Settings']['SMTP Notifications']) {
        $fromEmail = $config_values['Settings']['From Email'];
        $toEmail = $config_values['Settings']['To Email'];
        $smtpPort = $config_values['Settings']['SMTP Port']; //TODO validate port is blank or a number between 0 and 65535

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
                    twxa_debug("From Email is invalid, using To Email as From Email\n", 0);
                    $fromEmail = $toEmail;
                }
                $email->From = $fromEmail;
                //$email->FromName = "torrentwatch-xa";
                // prepare the HELO FQDN from the From Email
                $splitEmail = explode('@', $fromEmail);
                $getMX = dns_get_record($splitEmail[1], "DNS_MX");
                if (isset($getMX['target'])) {
                    $helo = $getMX['target'];
                    twxa_debug("Detected HELO from From Email: $helo\n", 2);
                } else if ($splitEmail[1]) {
                    $helo = $splitEmail[1];
                    twxa_debug("Detected HELO from From Email: $helo\n", 2);
                } else {
                    $helo = "localhost.localdomain";
                    twxa_debug("Unable to detect HELO from From Email, using default: $helo\n", 2);
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

                $mail = @file_get_contents("templates/email.tpl"); //TODO use webDir because rss_dl.php can't access this
                $mail = str_replace('[MSG]', $msg, $mail);
                if (empty($mail)) {
                    $mail = $msg;
                }
                $email->Body = $mail;

                if (!$email->Send()) {
                    twxa_debug("Email failed; PHPMailer error: " . print_r($email->ErrorInfo, true) . "\n", 0);
                } else {
                    twxa_debug("Mail sent to $toEmail with subject: $subject via: " . $config_values['Settings']['SMTP Server'] . "\n", 1); //TODO redo verbiage
                }
            } else {
                // To Email is not valid
                twxa_debug("Cannot send email: required To Email is not valid\n", -1);
            }
        } else {
            // SMTP Port is not valid
            twxa_debug("Cannot send email: SMTP Port is not valid; leave blank for default of 25 or provide integer from 0-65535\n", -1);
        }
    /*}
    else {
        twxa_debug("Not using SMTP Notifications\n", 2);
    }*/
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
            twxa_debug("Notify Script is not a single file; ignoring for security reasons.\n", -1);
            return;
        }
        twxa_debug("Running script: $script $param $torrent $error \n", 1);
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
            twxa_debug("$msg\n", 0);
        }
    }
}

function check_for_cookies($url) {
    if ($cookies = stristr($url, ':COOKIE:')) {
        $url = rtrim(substr($url, 0, -strlen($cookies)), '&');
        $cookies = strtr(substr($cookies, 8), '&', ';');
        return array('url' => $url, 'cookies' => $cookies);
    }
}

function getClientData($recent) {
    if ($recent) {
        $request = array('arguments' => array('fields' => array('id', 'name', 'errorString', 'hashString',
                    'leftUntilDone', 'downloadDir', 'totalSize', 'uploadedEver', 'downloadedEver', 'addedDate', 'status', 'eta',
                    'peersSendingToUs', 'peersGettingFromUs', 'peersConnected', 'seedRatioLimit', 'recheckProgress', 'rateDownload', 'rateUpload'),
                'ids' => 'recently-active'), 'method' => 'torrent-get');
    } else {
        $request = array('arguments' => array('fields' => array('id', 'name', 'errorString', 'hashString',
                    'leftUntilDone', 'downloadDir', 'totalSize', 'uploadedEver', 'downloadedEver', 'addedDate', 'status', 'eta',
                    'peersSendingToUs', 'peersGettingFromUs', 'peersConnected', 'seedRatioLimit', 'recheckProgress', 'rateDownload', 'rateUpload')),
            'method' => 'torrent-get');
    }
    $response = transmission_rpc($request);
    return json_encode($response);
}

function delTorrent($torHash, $trash, $batch = false) {
    global $config_values;

    if ($batch) {
        $torHash = explode(',', $torHash);
    }

    $request = array('arguments' => array('delete-local-data' => $trash, 'ids' => $torHash), 'method' => 'torrent-remove');
    $response = transmission_rpc($request);
    return json_encode($response);
}

function stopTorrent($torHash, $batch = false) {
    global $config_values;

    if ($batch) {
        $torHash = explode(',', $torHash);
    }

    $request = array(
        'arguments' => array(
            'ids' => $torHash),
        'method' => 'torrent-stop'
    );
    $response = transmission_rpc($request);
    return json_encode($response);
}

function startTorrent($torHash, $batch = false) {
    global $config_values;

    if ($batch) {
        $torHash = explode(',', $torHash);
    }

    $request = array('arguments' => array('ids' => $torHash), 'method' => 'torrent-start');
    $response = transmission_rpc($request);
    return json_encode($response);
}

function moveTorrent($location, $torHash, $batch = false) {
    global $config_values;

    if ($batch) {
        $torHash = explode(',', $torHash);
    }

    $request = array('arguments' => array('fields' => array('leftUntilDone', 'totalSize'), 'ids' => $torHash), 'method' => 'torrent-get');
    $response = transmission_rpc($request);
    $totalSize = $response['arguments']['torrents']['0']['totalSize'];
    $leftUntilDone = $response['arguments']['torrents']['0']['leftUntilDone'];
    if (isset($totalSize) && $totalSize > $leftUntilDone) {
        $move = true;
    } else {
        $move = false;
    }

    $request = array('arguments' => array('location' => $location, 'move' => $move, 'ids' => $torHash), 'method' => 'torrent-set-location');
    $response = transmission_rpc($request);
    return json_encode($response);
}

function authenticate() {
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
}
