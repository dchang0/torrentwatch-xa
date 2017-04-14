<?php

global $config_values;

// PHP JSON, PHP cURL, and PHP mbstring support are assumed to be installed
require_once("/var/lib/torrentwatch-xa/lib/tools.php");
require_once("/var/lib/torrentwatch-xa/lib/atomparser.php");
require_once("/var/lib/torrentwatch-xa/lib/cache.php");
require_once("/var/lib/torrentwatch-xa/lib/class.bdecode.php");
require_once("/var/lib/torrentwatch-xa/lib/class.phpmailer.php");
require_once("/var/lib/torrentwatch-xa/lib/class.smtp.php"); // keep paired with require_once("class.phpmailer.php")
if (file_exists('/var/lib/torrentwatch-xa/config.php')) { //TODO set to use baseDir;
    require_once("/var/lib/torrentwatch-xa/config.php"); //TODO doesn't config.php always have to exist?
}
require_once("/var/lib/torrentwatch-xa/lib/feeds.php");
require_once("/var/lib/torrentwatch-xa/lib/twxa_html.php");
require_once("/var/lib/torrentwatch-xa/lib/lastRSS.php");
require_once("/var/lib/torrentwatch-xa/lib/tor_client.php");
require_once("/var/lib/torrentwatch-xa/lib/twxa_parse.php");

$config_values['Global'] = []; //TODO why do we need this?

function _isset($array, $key, $default = '') { //TODO rename this
    // checks array: if a key is set, return value or default
    return isset($array[$key]) ? $array[$key] : $default;
}

function my_strpos($haystack, $needle) {
    $pieces = explode(" ", $needle);
    foreach ($pieces as $n) {
        if (strpos($haystack, $n) !== false) {
            return true;
        }
    }
    return false;
}

function symlink_force($source, $dest) {
    if (file_exists($dest)) {
        unlink($dest);
    }
    symlink($source, $dest);
}

/* does not appear to do anything since $config_values['Global']['Unlink'] is never set
 * function unlink_temp_files() {
  global $config_values;
  if (isset($config_values['Global']['Unlink'])) {
  foreach ($config_values['Global']['Unlink'] as $file) {
  unlink($file);
  }
  }
  } */

/* already a built-in PHP function
 * if (!function_exists('fnmatch')) {

    function fnmatch($pattern, $string) {
        return @preg_match(
          '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?', '[' => '\[', ']' => '\]')) . '$/i', $string
          );
    }

}*/

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

function twxa_debug($string, $lvl = -1) {
    global $config_values;

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
            break;
    }

    // write plain text to log file
    file_put_contents('/tmp/twxalog', date("c") . " $errLabel $string", FILE_APPEND); //TODO don't hard-code the log file location

    if ($config_values['Settings']['debugLevel'] >= $lvl) { //TODO what is this block for, is it for Javascript alerts only?
        if (isset($config_values['Global']['HTMLOutput'])) {
            // write HTML output
            if ($lvl === -1) {
                $string = trim(strtr($string, array("'" => "\\'")));
                $debug_output = "<script type='text/javascript'>alert('$string');</script>";
            } else {
                $debug_output = date("c") . " $errLabel $string";
            }
        } else {
            // write plain text output
            echo(date("c") . " $errLabel $string");
        }
    }
}

function add_history($ti) {
    global $config_values;
    if (file_exists($config_values['Settings']['History'])) {
        $history = unserialize(file_get_contents($config_values['Settings']['History']));
    }
    $history[] = array('Title' => $ti, 'Date' => date("Y.m.d H:i"));
    file_put_contents($config_values['Settings']['History'], serialize($history));
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

function check_for_torrents($directory, $dest) {
    $handle = opendir($directory);
    if ($handle) {
        while (false !== ($file = readdir($handle))) {
            $ti = substr($file, 0, strrpos($file, '.') - 1);
            if (preg_match('/\.torrent$/', $file) && client_add_torrent("$directory/$file", $dest, $ti)) { //TODO client_add_torrent() returns string errors
                unlink("$directory/$file");
            }
        }
        closedir($handle);
    } else {
        twxa_debug("Cannot read directory: $directory\n", -1);
    }
}
