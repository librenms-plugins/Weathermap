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

// Import the config.inc.php file for variables
include 'config.inc.php';

if (php_sapi_name() != 'cli') 
{
    echo "ERROR: map-poller.php should ONLY be run as a CGI script!\n";
    exit;
}

// -d option for debug
$options = getopt("d");
if (isset($options['d']))
{
    echo("DEBUG!\n");
    $debug = TRUE;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    #ini_set('error_reporting', E_ALL ^ E_NOTICE);
} 
else 
{
    $debug = FALSE;
    #ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    ini_set('error_reporting', 0);
}

// Load the librenms config
chdir($librenms_base);
$config = json_decode(`./config_to_json.php`, true);

// Change to directory that map-poller is in.
chdir(__DIR__);

if (is_dir($conf_dir))
{
    if ($dh = opendir($conf_dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            if ("." != $file && ".." != $file && ".htaccess" != $file && "index.php" != $file)
            {
                $cmd = "php ./weathermap.php --config $conf_dir/$file --base-href $basehref";

                if (!empty($config['rrdcached']))
                {
                    $cmd = $cmd." --daemon ".$config['rrdcached']." --chdir ''";
                }
                else
                {
                    $cmd = $cmd." --chdir ".$config['rrd_dir'];
                }

                $fp = popen($cmd, 'r');

                while (!feof($fp))
                {
                    $read = fgets($fp);
                    echo $read;
                }
                pclose($fp);
            }
        }
    }
}
?>
