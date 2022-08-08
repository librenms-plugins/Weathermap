<?php

// sensible defaults

$conf_dir = 'configs/';
$mapdir=dirname(__FILE__)."/". $conf_dir;

$librenms_base = realpath(dirname(__FILE__) . '/../../../');
$librenms_url = '/';
$ignore_librenms=FALSE;
$configerror = '';

// Absolute route for the rrd directory
$rrd_default_path1 = $librenms_base . '/'.'rrd';

// Loaction of drawn maps 
$weathermap_output = $librenms_base . '/'.'output';

$config_loaded = @include_once 'editor-config.php';

// these are all set via the Editor Settings dialog, in the editor, now.
$use_overlay = FALSE; // set to TRUE to enable experimental overlay showing VIAs
$use_relative_overlay = FALSE; // set to TRUE to enable experimental overlay showing relative-positioning
$grid_snap_value = 0; // set non-zero to snap to a grid of that spacing


$basehref='/plugins/Weathermap/';


