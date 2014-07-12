<?php

if(! is_writable('plugins/Weathermap/configs'))
{
  print_error("The map config directory is not writable by the web server user. You will not be able to edit any files until this is corrected.");
}
echo ('Click <a href="plugins/Weathermap/editor.php">here to access the editor</a> where you can create and manage maps. <h3>Please ensure you follow the installation instructions below.</h3>');
$readme = @file_get_contents('plugins/Weathermap/INSTALL.md');
$readme = nl2br($readme);
echo('<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Installation instructions</h3>
  </div>
  <div class="panel-body">
    <code>
      '.$readme.'
    </code>
  </div>
</div>');

?>
