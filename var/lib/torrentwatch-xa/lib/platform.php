<?php

$platform = "Linux";

function platform_initialize() {
    global $platform; //TODO what does this function do?
}

function platform_getConfigFile() {
    return platform_get_configCacheDir() . "/torrentwatch-xa.config";
}

function platform_getConfigCache() {
    return platform_get_configCacheDir() . "/torrentwatch-xa-config.cache";
}
