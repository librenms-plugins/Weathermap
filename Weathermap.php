<?php

namespace LibreNMS\Plugins;

include_once 'lib/editor.inc.php';

class Weathermap {

  public static function menu() {
        //Include config
        include_once 'config.inc.php';

	//Parse config files
        $files = list_weathermaps($mapdir);

	//Create submenu 
	$submenu = ' <ul class="dropdown-menu scrollable-menu">';
	$count = 0;

        //getShortName - might make sense to include this ShortName code into Plugin class somewhere ?
	if ($pos = strrpos(get_class(), '\\')) { 
            $short= substr(get_class(), $pos + 1);
        } else { 
            $short = $pos;
        };

	foreach ($files as $file=>$data) {
            $nicefile = htmlspecialchars($file);
            $submenu .= '   <li><a href="/plugins/'.$short.'/'.htmlspecialchars($data['page']).'"><i class="fa fa-map fa-fw fa-lg" aria-hidden="true"></i> '.htmlspecialchars($data['title']).'</a></li>';
            $count ++;
        }
	$submenu .= ' </ul>';

        //Display it if not empty
        if ($count > 0) {
            echo('<li class="dropdown-submenu"><a href="plugin/p='.$short.'">'.$short.'</a>');
            echo $submenu;
            echo ('</li>');
        } else {
            //Create menu without submenu
            echo('<li><a href="plugin/p='.$short.'">'.$short.'</a></li>');
        }
  }
}
?>
