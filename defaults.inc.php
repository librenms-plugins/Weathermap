<?php

/*
 * By default the data-picker shows interfaces ORDERed by their ifAlias.
 * This might be confusing at times. Now it's possible to configure the
 * data picker to order the interface list by one of
 *
 *   'ifAlias', 'ifDescr', 'ifIndex', 'ifName'
 *
 * by setting
 *
 *  $config['plugins']['Weathermap']['sort_if_by']
 *
 * in the libreNMS config.php file to the according value.
 */
$config['plugins']['Weathermap']['sort_if_by'] = 'ifAlias';


/* By default all interfaces of all devices are loaded and listed when
 * opening the data-picker. In large setups this will cause high loading
 * times every time the data-picker is loaded. With the following option
 * this behvaiour can be changed, valid values are:
 *
 *  'all', 'any', '-1'	Show all ports (default)
 *  'none', '0'		Don't load any ports.
 *   <integer> >= 0	Show port of libreNMS $device_id
 *
 */
$config['plugins']['Weathermap']['show_interfaces'] = 'all';
?>
