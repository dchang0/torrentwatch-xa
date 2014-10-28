<?php

define ("CURLOPT_URL",1);
define ("CURLOPT_USERPWD",2);
define ("CURLOPT_HTTPHEADER",3);
define ("CURLOPT_POSTFIELDS",4);
define ("CURLOPT_CONNECTTIMEOUT",5);
define ("CURLOPT_TIMEOUT",6);
define ("CURLOPT_RETURNTRANSFER",7);
define ("CURLOPT_COOKIE",8);
define ("CURLOPT_NOBODY",9);
define ("CURLOPT_USERAGENT",10);
define ("CURLOPT_HEADER",11);
define ("CURLINFO_HTTP_CODE",12);
define ("CURLOPT_FOLLOWLOCATION",13);
define ("CURLINFO_CONTENT_TYPE", "content-type");

function curl_init() {    
	global $curl_stuff;
	$id=time().rand(100,100000);
	$curl_stuff[$id]=array();
	return($id);
}

function curl_setopt_array($sess, $options) {
	global $curl_stuff;
	$curl_stuff[$sess]=$options;
}

function curl_exec ($sess) {
	global $curl_stuff;

	$url=$curl_stuff[$sess][CURLOPT_URL];

	$header="";

	if (isset($curl_stuff[$sess][CURLOPT_POSTFIELDS])) {
		$method="POST";
		$content=$curl_stuff[$sess][CURLOPT_POSTFIELDS];
	} else {
		$method="GET";
	}
	if (isset($curl_stuff[$sess][CURLOPT_NOBODY]) && $curl_stuff[$sess][CURLOPT_NOBODY]) {
		$method = "HEAD";
	}
	$http=array('method' => $method);

	if (isset($curl_stuff[$sess][CURLOPT_HTTPHEADER]) && (is_array($curl_stuff[$sess][CURLOPT_HTTPHEADER]))) {
     	    foreach ($curl_stuff[$sess][CURLOPT_HTTPHEADER] as $value) {
		if (!preg_match("/POST/", $value) && !preg_match("/Content-Length:/", $value) && !preg_match("/Python/", $value)) {	
            	    $header.="$value\r\n";
		}	
      	    }
	}
	if (isset($curl_stuff[$sess][CURLOPT_USERPWD]) && ($curl_stuff[$sess][CURLOPT_USERPWD] == ":")) {
		$curl_stuff[$sess][CURLOPT_USERPWD]="";
	}

	if (isset($curl_stuff[$sess][CURLOPT_USERPWD])) {
		$header.='Authorization: Basic '.base64_encode($curl_stuff[$sess][CURLOPT_USERPWD])."\r\n";
	}
	
	if(isset($curl_stuff[$sess][CURLOPT_COOKIE])) {
	    $header .= 'Cookie: ' . $curl_stuff[$sess][CURLOPT_COOKIE] . "\r\n";
	}
        if(isset($curl_stuff[$sess][CURLOPT_USERAGENT])) {
	    $http['user_agent'] = $curl_stuff[$sess][CURLOPT_USERAGENT];
        }

	if(isset($curl_stuff[$sess][CURLOPT_TIMEOUT])) {
		$http['timeout'] = $curl_stuff[$sess][CURLOPT_TIMEOUT];
    	}

	if (isset($header)) {
		$http['header']=$header;
	}
	if (isset($content)) {
		$http['content']=$content;
	}
	$params=array('http' => $http);
	$context=stream_context_create($params);
	if (!$result=@file_get_contents($url,false,$context)) {
		$result=$http_response_header[0];
	}
	
	$curl_stuff[$sess]['headers'] = $http_response_header;
	
	if (isset($curl_stuff[$sess][CURLOPT_HEADER])) {
		  $data = '';
          foreach ($http_response_header as $value) {
                  $data.="$value\r\n";
          }
          $result=$data;
   	}
	//_debug("BLA: " . $header . "\n");
	//$out.=$url."\n".$header."\n".$content."\n".$method."\n".$result."\n";;
	return ($result);
}

function curl_getinfo($sess, $ch) {
	global $curl_stuff;
	$value = null;
	if($ch === 12 ) {
	    $values = explode(" ", $curl_stuff[$sess]['headers'][0]);
	    $value = $values[1];
	} else {
	    foreach ($curl_stuff[$sess]['headers'] as $header) {
		$split = explode(":", $header);
		if (count($split)==2 && strtolower($split[0])==$ch) {
			$value = strtolower(trim($split[1]));
			break;
		}
	    }
	}
	return $value;
}

function curl_close($sess) {
	global $curl_stuff;
	$curl_stuff[$sess]=array();
}

?>
