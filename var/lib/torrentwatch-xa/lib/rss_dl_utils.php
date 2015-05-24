<?php

global $config_values, $platform;

require_once("tools.php");
require_once("atomparser.php");
require_once("cache.php");
require_once("class.bdecode.php");
require_once("class.phpmailer.php");
if (!extension_loaded("curl")) {
    require_once("curl.php");
}
//if (file_exists(dirname(__FILE__) . '/config.php')) { //TODO set to use baseDir;
if (file_exists('/var/lib/torrentwatch-xa/config.php')) { //TODO set to use baseDir;
    require_once("/var/lib/torrentwatch-xa/config.php");
}
require_once("feeds.php");
require_once("html.php");
require_once("lastRSS.php");
require_once("tor_client.php");
require_once("platform.php");
require_once("guess.php");

$config_values['Global'] = [];
$time = 0;

// Checks array is a key is set, return value or default
function _isset($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}

function my_strpos($haystack, $needle) {
    $pieces = explode(" ", $needle);
    foreach ($pieces as $n) {
        if (strpos($haystack, $n) !== FALSE)
            return TRUE;
    }
    return FALSE;
}

function symlink_force($source, $dest) {
    if (file_exists($dest)) {
        unlink($dest);
    }
    symlink($source, $dest);
}

function unlink_temp_files() {
    global $config_values;
    if (isset($config_values['Global']['Unlink']))
        foreach ($config_values['Global']['Unlink'] as $file)
            unlink($file);
}

if (!function_exists('fnmatch')) {

    function fnmatch($pattern, $string) {
        return @preg_match(
                        '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?', '[' => '\[', ']' => '\]')) . '$/i', $string
        );
    }

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
            //maybee lcfirst is not callable
            if (!function_exists('lcfirst'))
                $function = create_function('$input', 'return strToLower($input[0]) . substr($input, 1, (strLen($input) - 1));');
            else
                $function = 'lcfirst';
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
        if (is_array($value)) //$value is an array, handle keys too
            $newArray[$function($key)] = array_change_key_case_ext($value, $case);
        elseif (is_string($key))
            $newArray[$function($key)] = $value;
        else
            $newArray[$key] = $value; //$key is not a string
    } //end loop
    return $newArray;
}

function twxa_debug($string, $lvl = -1) {
    global $config_values, $debug_output; //TODO fix this!!!
    file_put_contents('/tmp/twlog', $string, FILE_APPEND);

    if ($config_values['Settings']['debugLevel'] >= $lvl) {
        if (isset($config_values['Global']['HTMLOutput'])) {
            if ($lvl == -1) {
                $string = trim(strtr($string, array("'" => "\\'")));
                $debug_output .= "<script type='text/javascript'>alert('$string');</script>";
            } else {
                $debug_output .= $string;
            }
        } else {
            echo($string);
        }
    }
}

function add_history($title) {
    global $config_values;
    if (file_exists($config_values['Settings']['History']))
        $history = unserialize(file_get_contents($config_values['Settings']['History']));
    $history[] = array('Title' => $title, 'Date' => date("Y.m.d G:i"));
    file_put_contents($config_values['Settings']['History'], serialize($history));
}

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

function timer_init() {
    global $time_start;
    return $time_start = microtime_float();
}

function timer_get_time($time = NULL) {
    global $time_start;
    if ($time == NULL)
        $time = $time_start;
    return (microtime_float() - $time);
}

// Makes a name fit for use as a filename
function filename_encode($filename) {
    return preg_replace("/\?|\/|\\|\+|\=|\>|\<|\,|\"|\*|\|/", "_", $filename);
}

function check_for_torrents($directory, $dest) {
    if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
            $title = substr($file, 0, strrpos($file, '.') - 1);
            if (preg_match('/\.torrent$/', $file) && client_add_torrent("$directory/$file", $dest, $title))
                unlink("$directory/$file");
        }
        closedir($handle);
    } else {
        twxa_debug("check_for_torrents: Couldn't read Directory: $directory\n", 0);
    }
}
