<?php

// sensible defaults
$mapdir=dirname(__FILE__)."/configs/";

$librenms_base = '../../../';
$librenms_url = '/';
$ignore_librenms=FALSE;
$configerror = '';

$config_loaded = @include_once 'editor-config.php';

// these are all set via the Editor Settings dialog, in the editor, now.
$use_overlay = FALSE; // set to TRUE to enable experimental overlay showing VIAs
$use_relative_overlay = FALSE; // set to TRUE to enable experimental overlay showing relative-positioning
$grid_snap_value = 0; // set non-zero to snap to a grid of that spacing

