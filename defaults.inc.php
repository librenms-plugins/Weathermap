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

?>
