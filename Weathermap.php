<?php

namespace LibreNMS\Plugins;

class Weathermap {

  public function menu() {
    echo('<li><a href="plugin/p='.get_class().'">'.get_class().'</a></li>');
  }

}

?>

