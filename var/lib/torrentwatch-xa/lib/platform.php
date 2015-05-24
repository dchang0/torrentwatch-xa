<?php

if(file_exists("/etc/init_nmt") || is_dir("/nmt/apps"))
  $platform = "NMT";
else if(is_dir("/Library/WebServer"))
  $platform = "OSX";
else
  $platform = "Linux";

function platform_initialize() {
  global $platform;
}

function platform_getConfigFile() {
  return platform_get_configCacheDir() . "/torrentwatch-xa.config";
}

function platform_getConfigCache() {
  return platform_get_configCacheDir() . "/torrentwatch-xa-config.cache";
}

?>
