<?php
/*
 * client.php
 * Client specific functions
 */


function transmission_sessionId() {
  global $config_values, $platform;
  $sessionIdFile = get_tr_sessionIdFile();
  if(file_exists($sessionIdFile) && !is_writable($sessionIdFile)) {
      $myuid = posix_getuid();
      echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$sessionIdFile is not writable for uid: $myuid</div>";
      return;
  }

  if(file_exists($sessionIdFile)) {
      if(filesize($sessionIdFile) > 0) {
        $handle = fopen($sessionIdFile, 'r');
        $sessionId = trim(fread($handle, filesize($sessionIdFile)));
    } else {
        unlink($sessionIdFile);
    }
  } else {
    $tr_user = $config_values['Settings']['Transmission Login'];
    $tr_pass = get_client_passwd();
    $tr_host = $config_values['Settings']['Transmission Host'];
    $tr_port = $config_values['Settings']['Transmission Port'];
    $tr_uri = $config_values['Settings']['Transmission URI'];


    $sid = curl_init();
    $curl_options = array(CURLOPT_URL => "http://$tr_host:$tr_port$tr_uri",
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_USERPWD => "$tr_user:$tr_pass"
                  );
    get_curl_defaults($curl_options);

    curl_setopt_array($sid, $curl_options);

    $header = curl_exec($sid);
    curl_close($sid);
  
    preg_match("/X-Transmission-Session-Id:\s(\w+)/",$header,$ID);

    if(isset($ID[1])) {
      $handle = fopen($sessionIdFile, "w");
      fwrite($handle, $ID[1]);
      fclose($handle);
      $sessionId = $ID[1];
    }
  }
  if(isset($sessionId))
    return $sessionId;
}

function transmission_rpc($request) {
  global $config_values;
  $sessionIdFile = get_tr_sessionIdFile();  
  if(file_exists($sessionIdFile) && !is_writable($sessionIdFile)) {
      $myuid = posix_getuid();
      echo "<div id=\"errorDialog\" class=\"dialog_window\" style=\"display: block\">$sessionIdFile is not writable for uid: $myuid</div>";
      return;
  }

  $tr_user = $config_values['Settings']['Transmission Login'];
  $tr_pass = get_client_passwd();
  $tr_uri = $config_values['Settings']['Transmission URI'];
  $tr_host = $config_values['Settings']['Transmission Host'];
  $tr_port = $config_values['Settings']['Transmission Port'];

  $request = json_encode($request);
  $reqLen = strlen("$request");
  
  $run = 1; 
  while($run) { 
    $SessionId = transmission_sessionId();

    $post = curl_init();
    $curl_options = array(CURLOPT_URL => "http://$tr_host:$tr_port$tr_uri",
                          CURLOPT_USERPWD => "$tr_user:$tr_pass",
                          CURLOPT_HTTPHEADER => array (
                                                "POST $tr_uri HTTP/1.1",
                                                "Host: $tr_host",
                                                "X-Transmission-Session-Id: $SessionId",
                                                'Connection: Close',
                                                "Content-Length: $reqLen",
                                                'Content-Type: application/json'
                                               ),
                          CURLOPT_POSTFIELDS => "$request"
                       );
    get_curl_defaults($curl_options);
    curl_setopt_array($post, $curl_options);
    $raw = curl_exec($post);
    curl_close($post);
    if(preg_match('/409:? Conflict/', $raw)) {
        if(file_exists($sessionIdFile)) {
            unlink($sessionIdFile);
        }
    } else {
      $run = 0;
    }
  }
  return json_decode($raw, TRUE);


}

function get_deep_dir($dest, $tor_name) {
    global $config_values;
    switch($config_values['Settings']['Deep Directories']) {
    case '0':
        break;
    case 'Title_Season':
          $guess = detectMatch($tor_name, TRUE);
          if(isset($guess['title']) && isset($guess['episode'])) {
            if(preg_match('/^(\d{1,3})x\d+p?$/', $guess['episode'], $Season)) {
                $dest = $dest."/".ucwords(strtolower($guess['title']))."/Season ".$Season[1];
            } else if(preg_match('/^(\d{4})\d{4}$/', $guess['episode'], $Year)) {
                $dest = $dest."/".ucwords(strtolower($guess['title']))."/".$Year[1];
            } else {
                $dest = $dest."/".ucwords(strtolower($guess['title']));
            }
            break;
          }
          twxa_debug("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
    case 'Title':
        $guess = detectMatch($tor_name, TRUE);
        if(isset($guess['title'])) {
          $dest = $dest."/".ucwords(strtolower($guess['title']));
          break;
        }
        twxa_debug("Deep Directories: Couldn't match $tor_name Reverting to Full\n", 1);
    case 'Full':
    default:
        $dest = $dest."/".ucwords(strtolower($tor_name));
        break;
    }
    return $dest;
}

function folder_add_torrent($tor, $dest, $ti) {
  global $config_values;
  // remove invalid chars
  $ti = strtr($ti, '/', '_');
  // add the directory and extension
  $dest = "$dest/$ti.".$config_values['Settings']['Extension'];
  // save it
  file_put_contents($dest, $tor);
  return 0;
}

function transmission_add_torrent($tor, $dest, $ti, $seedRatio) {
  global $config_values;
  // transmission dies with bad folder if it doesn't end in a /
  if(substr($dest, strlen($dest)-1, 1) != '/')
    $dest .= '/';

  if(preg_match('/^magnet:/', $tor)) {
    $request = array('method' => 'torrent-add',
                   'arguments' => array('download-dir' => $dest,
			    		'filename' => $tor
                                       )
                               );
  } else {
    $request = array('method' => 'torrent-add',
                   'arguments' => array('download-dir' => $dest,
    					'metainfo' => base64_encode($tor)
                                       )
                               );
  }
  $response = transmission_rpc($request);

  $torHash = $response['arguments']['torrent-added']['hashString'];

  if(isset($response['result']) AND ($response['result'] == 'success')) {
    $cache = $config_values['Settings']['Cache Dir'] . "/rss_dl_" . filename_encode($ti);
    if($torHash) {
      $handle = fopen("$cache", "w");
      fwrite($handle, $torHash);
      fclose($handle);
    }
    if($seedRatio >= 0 && ($torHash)) {
      $request = array('method' => 'torrent-set',
             'arguments' => array('ids' => $torHash,
             'seedRatioLimit' => $seedRatio,
             'seedRatioMode' => 1)
            );
      $response = transmission_rpc($request);
      if($response['result'] != 'success') {
	twxa_debug("Failed setting ratio limit for $ti\n");
      }
  } 

    return 0;
  } else if ($response['result'] == 'duplicate torrent') {
      return "Duplicate Torrent";
  } else {
    if(!isset($response['result']))
      return "Failure connecting to Transmission";
    else
      return "Transmission RPC Error: ".print_r($response, TRUE);
  }
}

function client_add_torrent($filename, $dest, $ti, $feed = NULL, &$fav = NULL, $retried=false) {
  global $config_values, $hit, $twxa_version;
  if(strtolower($fav['Filter']) == "any") $any=1;
  $hit = 1;

  if(preg_match("/^magnet:/", $filename)) { 
    $tor = $filename;
    $magnet = 1;
  }

  if(!$magnet) {
    $filename = htmlspecialchars_decode($filename);

    // Detect and append cookies from the feed url
    $url = $filename;
    if($feed && preg_match('/:COOKIE:/', $feed) && (!(preg_match('/:COOKIE:/', $url)))) {
      $url .= stristr($feed, ':COOKIE:');    
    }

    $get = curl_init();
    $response = check_for_cookies($url);
    if($response) {
      $url = $response['url'];
      $cookies = $response['cookies'];
    }
    $getOptions[CURLOPT_URL] = $url;
    if(isset($cookies)) $getOptions[CURLOPT_COOKIE] = $cookies;
    //$getOptions[CURLOPT_USERAGENT] = 'Python-urllib/1.17';  
    $getOptions[CURLOPT_USERAGENT] = "torrentwatch-xa/$twxa_version[0]";  
    get_curl_defaults($getOptions);
    curl_setopt_array($get, $getOptions);
    $tor = curl_exec($get);
    curl_close($get);

    if (strncasecmp($tor, 'd8:announce', 11) != 0) { // Check for torrent magic-entry
	//This was not a torrent-file, so it's probably some kind of xml / html.
	if(!$retried) {
	    //Try to retrieve a .torrent link from the content.
	    $link = find_torrent_link($url, $tor);
	    return client_add_torrent($link, $dest, $ti, $feed, $fav, $url);
	} else {
	    twxa_debug("No torrent file found on $url. Exiting.\n");
	    if(isset($retried)) $url = $retried;
	    return "Error: No torrent file found on $url. Might be a gzipped torrent.";
	}
    }

    if(!$tor) {
      print '<pre>'.print_r($_GET, TRUE).'</pre>';
      twxa_debug("Couldn't open torrent: $filename \n",-1);
      return "Error: Couldn't open torrent: $filename";
    }
  }
  
  $tor_info = new BDecode("", $tor);
  if(!($tor_name = $tor_info->{'result'}['info']['name'])) {
    $tor_name = $ti;
  }

  if(!isset($dest)) {
    $dest = $config_values['Settings']['Download Dir'];
  }
  if(isset($fav) && $fav['Save In'] != 'Default') {
    $dest = $fav['Save In'];
  }

  $dest = get_deep_dir(preg_replace('/\/$/', '', $dest), $tor_name);

  if(!file_exists($dest) or !is_dir($dest)) {
    $old_umask = umask(0);
    if(file_exists($dest))
      unlink($dest);
    mkdir($dest, 0777, TRUE); //TODO fix this--probably better to simply change permissions on existing dir rather than delete and recreate
    umask($old_umask);
  }
  
  foreach($config_values['Feeds'] as $key => $feedLink) {
      if($feedLink['Link'] == "$feed") $idx = $key;
  }
  if($config_values['Feeds'][$idx]['seedRatio'] >= 0) {
      $seedRatio = $config_values['Feeds'][$idx]['seedRatio'];
  } else {
      $seedRatio = $config_values['Settings']['Default Seed Ratio'];
  }
  if(!($seedRatio)) $seedRatio = -1;
  
  switch($config_values['Settings']['Client']) {
    case 'Transmission':
      $return = transmission_add_torrent($tor, $dest, $ti, _isset($fav, '$seedRatio', $seedRatio));
      break;
    case 'folder':
      if($magnet) {
	twxa_debug("Can not save magnet links to a folder\n");
      } else {
	$return = folder_add_torrent($tor, $dest, $tor_name);
      }
      break;
    default:
      twxa_debug("Invalid Torrent Client: ".$config_values['Settings']['Client']."\n",-1);
      exit(1);
  }
  if($return === 0) {
    add_history($tor_name);
    twxa_debug("Started: $tor_name in $dest\n",0);
    if(isset($fav)) {
      run_script('favstart', $ti);
      if($config_values['Settings']['Email Notifications'] == 1) {
          $subject = "torrentwatch-xa: $tor_name started downloading.";
          $msg = "torrentwatch-xa started downloading $tor_name";
          MailNotify($msg, $subject);
      }
      if(!$any) {
	updateFavoriteEpisode($fav, $ti);
        twxa_debug("Updated Favorites\n");
      }
    } else {
        run_script('nonfavstart', $ti);
    }
    if($config_values['Settings']['Save Torrents'])
      file_put_contents("$dest/$tor_name.torrent", $tor);
    return "Success";
  } else {
    twxa_debug("Failed Starting: $tor_name  Error: $return\n",-1);

    $msg = "torrentwatch-xa tried to start \"$tor_name\". But this failed with the following error:\n\n";
    $msg.= "$return\n";

    $subject = "torrentwatch-xa: Error while trying to start $tor_name.";
    MailNotify($msg, $subject);
    run_script('error', $ti, $msg);
    return "Error: $return";
  }
}


function find_torrent_link($url_old, $content) {
	$url = "";
	if($ret = preg_match('/["\']([^\'"]*?\.torrent[^\'"]*?)["\']/', $content, $matches)) {
	    if (isset($ret)) {
		$url = $matches[1];
		if (!preg_match('/^https?:\/\//', $url)) {
			if (preg_match('^/', $url)) {
				$url = dirname($url_old) . $url;
			} else {
				$url = dirname($url_old) . '/' . $url;
			}
		}
	    }
	} else  {
	    $ret = preg_match_all('/href=["\']([^#].+?)["\']/', $content, $matches);
	    if ($ret) {
		foreach($matches[1] as $match) {
		    if (!preg_match('/^https?:\/\//', $match)) {
			if (preg_match('^/', $match)) {
			    $match = dirname($url_old) . $match;
			} else {
			    $match = dirname($url_old) . '/' . $match;
			}
		    }
		    if (preg_match('/w3.org/i', $match)) {
			break;
		    }
		    $opts = array('http' =>
			array('timeout'=>10)
		    );
		    stream_context_get_default($opts);
		    $headers = get_headers($match, 1);
		    if((isset($headers['Content-Disposition']) && 
		      preg_match('/filename=.+\.torrent/i', $headers['Content-Disposition'])) ||
		      (isset($headers['Content-Type']) &&
		      $headers['Content-Type'] == 'application/x-bittorrent' )) {
			    $url = $match;
		    }
		}
	    }
	}
	return $url;
}
