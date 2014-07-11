<?php

if(! is_writable('configs'))
{
  print_error("The map config directory is not writable by the web server user. You will not be able to edit any files until this is corrected.");
}
print_message("Please ensure that you enable the editor for use, to do this please edit editor.php and set \$ENABLED=true. Using Weathermap as a LibreNMS plugin means editor.php is actually using LibreNMS to authenticate anyway so unlike Weathermap on it's own, you don't actually need to disabled the editor when it's not in use.");
echo ('Please go to the <a href="plugins/Weathermap/editor.php">editor</a> to manage your Weathermaps');

?>
