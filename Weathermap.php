<?php

include_once 'lib/editor.inc.php';

class Weathermap {

  public static function menu() {

include_once 'config.inc.php';

	echo('<li class="dropdown-submenu"><a href="plugin/p='.get_class().'">'.get_class().'</a>');
	$files = list_weathermaps($mapdir);
	echo (' <ul class="dropdown-menu scrollable-menu">');
	foreach ($files as $file=>$data) {
            $nicefile = htmlspecialchars($file);
            echo '<li><a href="/plugins/'.get_class().'/'.htmlspecialchars($data['page']).'"><i class="fa fa-map fa-fw fa-lg" aria-hidden="true"></i> '.htmlspecialchars($data['title']).'</a></li>';
        }
	echo (' </ul>');
   	echo ('</li>');
  }
}
?>
