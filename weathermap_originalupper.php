<?php

namespace LibreNMS\Plugins;

include_once 'lib/editor.inc.php';

class Weathermap
{
    public static $name = 'Weathermap';

    public static function menu()
    {

        //Include config
        include 'config.inc.php';

        //Parse config files
        $files = list_weathermaps($mapdir);

        //Create submenu
        $submenu = ' <ul class="dropdown-menu scrollable-menu">';
        $count = 0;
        foreach ($files as $file => $data) {
            $submenu .= '   <li><a href="/plugins/' . self::$name . '/' . htmlspecialchars($data['page']) . '"><i class="fa fa-map fa-fw fa-lg" aria-hidden="true"></i> ' . htmlspecialchars($data['title']) . '</a></li>';
            $count++;
        }
        $submenu .= ' </ul>';

        //Display it if not empty
        if ($count > 0) {
            echo('<li class="dropdown-submenu"><a href="plugin/p=' . self::$name . '">' . self::$name . '</a>');
            echo $submenu;
            echo('</li>');
        } else {
            //Create menu without submenu
            echo('<li><a href="plugin/p=' . self::$name . '">' . self::$name . '</a></li>');
        }
    }
}
