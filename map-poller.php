#!/usr/bin/env php
<?php

// Copyright (C) 2013 Neil Lathwood neil@lathwood.co.uk
// Copyright (C) 2016 Maximilan Wilhelm <max@rfc2324.org>
/**

This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

**/


// Set variables for map-poller.php
$basehref='/plugins/Weathermap/';

// Valid config.php paths
$config_file_paths = array (
	'/etc/librenms/config.php',
	'/opt/librenms/config.php',
	'../../../config.php',
);

if (php_sapi_name() != 'cli') {
	echo "ERROR: map-poller.php should ONLY be run as a CGI script!\n";
	exit;
}

$options = getopt("d");
if (isset($options['d']))
{
	echo("DEBUG!\n");
	$debug = TRUE;
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	ini_set('log_errors', 1);
# 	ini_set('error_reporting', E_ALL ^ E_NOTICE);
} else {
	$debug = FALSE;
#	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	ini_set('log_errors', 0);
	ini_set('error_reporting', 0);
}


// Change to directory that map-poller was run from.
// Thank you to Supun Rathnayake (https://twitter.com/supunr) for the bug report
// and fix for includes being set incorrectly and changing map-poller to chdir from
// where it's run.
chdir(dirname($argv[0]));

// Try to find most appropriate librenms config file and installation dir
$config_file_path = NULL;
$librenms_base = '../../../';

foreach ($config_file_paths as $path) {
	if (file_exists ($path)) {
		$config_file_path = $path;
		break;
	}
}

if (! $config_file_path) {
	echo "ERROR: map-poller.php: No valid libreNMS config.php file found!\n";
	echo "I looked at " . join (', ', $config_file_paths) . "\n";
	exit;
}

// Include config first to get install dir, then load defaults and config
// again to get full set of config values.
require ("$config_file_path");
$librenms_base = $config['install_dir'];

include("$librenms_base/includes/defaults.inc.php");
require("$librenms_base/config.php");
include("$librenms_base/includes/definitions.inc.php");
include("$librenms_base/includes/functions.php");
include("$librenms_base/includes/polling/functions.inc.php");

$conf_dir = 'configs/';

if (is_dir($conf_dir)) {
	if ($dh = opendir($conf_dir)) {
		while (($file = readdir($dh)) !== false) {
			if ("." != $file && ".." != $file && ".htaccess" != $file && "index.php" != $file) {
			    	$cmd = "php ./weathermap.php --config $conf_dir/$file --base-href $basehref --chdir ".$config['rrd_dir'];
                                if ($config['rrdcached']) {
                                    $cmd = $cmd." --daemon ".$config['rrdcached'];
                                } 
				$fp = popen($cmd, 'r'); 
				while (!feof($fp)) {
					$read = fgets($fp);
					echo $read;
				}
				pclose($fp);
			}
		}
	}
}
?>
