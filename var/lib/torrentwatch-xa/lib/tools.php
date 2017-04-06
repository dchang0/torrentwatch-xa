<?php

function MailNotify($msg, $subject) {
    global $config_values;

    $emailAddress = $config_values['Settings']['Email Address'];

    if (!empty($emailAddress)) {
        $email = new PHPMailer();

        if (function_exists('dns_get_record') && dns_get_record(gethostname())) {
            $email->From = "torrentwatch-xa@" . gethostname();
        } else {
            $email->From = "torrentwatch-xa@localhost";
        }
        $email->FromName = "torrentwatch-xa";
        $email->AddAddress("$emailAddress");
        $email->Subject = $subject;

        $email->Host = $config_values['Settings']['SMTP Server'];
        $email->Mailer = "smtp";

        $mail = @file_get_contents("templates/email.tpl");
        $mail = str_replace('[MSG]', $msg, $mail);
        if (empty($mail)) {
            $mail = $msg;
        }
        $email->Body = $mail;

        if (!$email->Send()) {
            twxa_debug("Mailer Error: " . $email->ErrorInfo . "\n");
        } else {
            twxa_debug("Mail sent to $emailAddress with subject: $subject via: " . $config_values['Settings']['SMTP Server'] . "\n");
        }
    }
}

function run_script($param, $torrent, $error = "") {
    global $config_values;
    $torrent = escapeshellarg($torrent);
    $error = escapeshellarg($error);
    $script = $config_values['Settings']['Script'];
    if ($script) {
        if (!is_file($script)) {
            $msg = "The configured script is not a single file. Parameters are not allowed because of security reasons.";
            $subject = "torrentwatch-xa: security error";
            MailNotify($msg, $subject);
            return;
        }
        twxa_debug("Running $script $param $torrent $error \n", -1);
        exec("$script $param $torrent $error 2>&1", $response, $return);
        if ($return && $config_values['Settings']['Email Address']) {

            $msg = "Something went wrong while running $script:\n";
            foreach ($response as $line) {
                $msg .= $line . "\n";
            }
            $msg.= "\n";
            $msg.= "Please visit 'https://github.com/dchang0/torrentwatch-xa/' for more info about how to make a compatible script.";

            twxa_debug("$msg\n");
            $subject = "torrentwatch-xa: $script returned error.";
            MailNotify($msg, $subject);
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

    $request = array('arguments' => array('ids' => $torHash), 'method' => 'torrent-stop');
    $response = transmission_rpc($request);
    twxa_debug(var_export($request, true));
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
    }
    else {
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
