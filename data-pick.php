<?php

// ******************************************
// sensible defaults
include 'config.inc.php';
$whats_installed = '';


$weathermap_config = array (
	'show_interfaces' => 'all',
	'sort_if_by' => 'ifAlias',
);

$valid_sort_if_by = array (
	'ifAlias',
	'ifDescr',
	'ifIndex',
	'ifName',
);

$valid_show_interfaces = array (
	'all' => -1,
	'any' => -1,
	'-1'  => -1,
	#
	'none' => 0,
	'0'    => 0,
);

	/*
	 * Include the LibreNMS config, so we know about the database.
	 *
	 * Include config first to get install dir, then load defaults and config
	 * again to get full set of config values.
	 */
	/* Load Weathermap config defaults, see file for description. */

    $init_modules = ['web', 'auth'];
    require $librenms_base . '/includes/init.php';

	if (!Auth::check()) {
		header('Location: /');
		exit;
	}

	chdir($librenms_base . '/plugins/Weathermap');
	$librenms_found = true;

	/* Validate configuration, see defaults.inc.php for explaination */
	if (in_array ($config['plugins']['Weathermap']['sort_if_by'], $valid_sort_if_by))
		$weathermap_config['sort_if_by'] = $config['plugins']['Weathermap']['sort_if_by'];

	if (in_array ($config['plugins']['Weathermap']['show_interfaces'], $valid_show_interfaces))
		$weathermap_config['show_interfaces'] = $valid_show_interfaces[$config['plugins']['Weathermap']['show_interfaces']];
	elseif (validate_device_id ($config['plugins']['Weathermap']['show_interfaces']))
		$weathermap_config['show_interfaces'] = $config['plugins']['Weathermap']['show_interfaces'];


// ******************************************

function js_escape($str)
{
	$str = str_replace('\\', '\\\\', $str);
	$str = str_replace("'", "\\\'", $str);

	$str = "'".$str."'";

	return($str);
}

if(isset($_REQUEST['command']) && $_REQUEST["command"]=='link_step2')
{
	$dataid = intval($_REQUEST['dataid']);


?>
<html>
<head>
	<script type="text/javascript">
	function update_source_step2(graphid)
	{
		var graph_url, hover_url;

		var base_url = '<?php echo isset($config['base_url'])?$config['base_url']:''; ?>';

		if (typeof window.opener == "object") {

			graph_url = base_url + 'graph.php?height=100&width=512&device=' + graphid + '&type=device_bits&legend=no';
			info_url = base_url + 'device/device=' + graphid +'/';

			opener.document.forms["frmMain"].node_new_name.value ='test';
			opener.document.forms["frmMain"].node_label.value ='testing';
			opener.document.forms["frmMain"].link_infourl.value = info_url;
			opener.document.forms["frmMain"].link_hover.value = graph_url;
		}
		self.close();
	}

	window.onload = update_source_step2(<?php echo $dataid ?>);

	</script>
</head>
<body>
This window should disappear in a moment.
</body>
</html>
<?php
	// end of link step 2
}

if(isset($_REQUEST['command']) && $_REQUEST["command"]=='link_step1')
{
?>
<html>
<head>
	<script type="text/javascript" src="vendor/jquery/dist/jquery.min.js"></script>
	<script type="text/javascript">

	function filterlist(previous)
	{
		var filterstring = $('input#filterstring').val();	
		
		if(filterstring=='')
		{
			$('ul#dslist > li').show();
			if($('#ignore_desc').is(':checked')) {
				$("ul#dslist > li:contains('Desc::')").hide();
			}
			return;
		
		} else if(filterstring!=previous)
		{	
				$('ul#dslist > li').hide();
				$("ul#dslist > li:contains('" + filterstring + "')").show();
				if($('#ignore_desc').is(':checked')) {
                         	       $("ul#dslist > li:contains('Desc::')").hide();
                        	}

		} else if(filterstring==previous)
		{
			if($('#ignore_desc').is(':checked')) {
                        	$("ul#dslist > li:contains('Desc::')").hide();
                        } else {
				$('ul#dslist > li').hide();
				$("ul#dslist > li:contains('" + filterstring + "')").show();
			}
		}

	}

	function filterignore()
	{
		if($('#ignore_desc').is(':checked')) {
			$("ul#dslist > li:contains('Desc::')").hide();
		} else {
			//$('ul#dslist > li').hide();
			$("ul#dslist > li:contains('" + previous + "')").show();
		}
	}

	$(document).ready( function() {
		$('span.filter').keyup(function() {
			var previous = $('input#filterstring').val();
			setTimeout(function () {filterlist(previous)}, 500);
		}).show();
		$('span.ignore').click(function() {
			var previous = $('input#filterstring').val();
			setTimeout(function () {filterlist(previous)}, 500);
		});
	});

        function update_source_step2(graphid,name,portid,ifAlias,ifDesc,ifIndex)
        {
                var graph_url, hover_url;

                var base_url = '<?php echo isset($config['base_url'])?$config['base_url']:''; ?>';

                if (typeof window.opener == "object") {

                        graph_url = base_url + 'graph.php?height=100&width=512&id=' + portid + '&type=port_bits&legend=no';
                        info_url = base_url + 'graphs/type=port_bits/id=' + portid +'/';

                        opener.document.forms["frmMain"].node_new_name.value ='test';
                        opener.document.forms["frmMain"].node_label.value ='testing';
                        opener.document.forms["frmMain"].link_infourl.value = info_url;
                        opener.document.forms["frmMain"].link_hover.value = graph_url;
                }
                self.close();
        }

	function update_source_step1(dataid,name,portid,ifAlias,ifDesc,ifIndex)
	{
		// This must be the section that looks after link properties
		var newlocation;
		var fullpath;

		var rra_path = <?php echo js_escape('./'); ?>+name+'/port-id';

		if (typeof window.opener == "object") {
			fullpath = rra_path+portid+'.rrd:INOCTETS:OUTOCTETS';
			if(document.forms['mini'].aggregate.checked)
			{
				opener.document.forms["frmMain"].link_target.value = opener.document.forms["frmMain"].link_target.value  + " " + fullpath;
			}
			else
			{
				opener.document.forms["frmMain"].link_target.value = fullpath;
			}
		}
		if(document.forms['mini'].overlib.checked)
		{

        		window.onload = update_source_step2(dataid,name,portid,ifAlias,ifDesc,ifIndex);

		}
		else
		{
			self.close();
		}
	}
	
	function applyDSFilterChange(objForm) {
                strURL = '?host_id=' + objForm.host_id.value;
                strURL = strURL + '&command=link_step1';
				if( objForm.overlib.checked)
				{
					strURL = strURL + "&overlib=1";
				}
				else
				{
					strURL = strURL + "&overlib=0";
				}
				// document.frmMain.link_bandwidth_out_cb.checked
				if( objForm.aggregate.checked)
				{
					strURL = strURL + "&aggregate=1";
				}
				else
				{
					strURL = strURL + "&aggregate=0";
				}
                document.location = strURL;
        }
	
	</script>
<style type="text/css">
	body { font-family: sans-serif; font-size: 10pt; }
	ul { list-style: none;  margin: 0; padding: 0; }
	ul { border: 1px solid black; }
	ul li.row0 { background: #ddd;}
	ul li.row1 { background: #ccc;}
	ul li { border-bottom: 1px solid #aaa; border-top: 1px solid #eee; padding: 2px;}
	ul li a { text-decoration: none; color: black; }
</style>
<title>Pick a data source</title>
</head>
<body>
<?php

	$host_id = $weathermap_config['show_interfaces'];
	
	$overlib = true;
	$aggregate = false;
	
	if(isset($_REQUEST['aggregate'])) $aggregate = ( $_REQUEST['aggregate']==0 ? false : true);
	if(isset($_REQUEST['overlib'])) $overlib= ( $_REQUEST['overlib']==0 ? false : true);
	
	/* Explicit device_id given? */
	if (isset ($_REQUEST['host_id']) and !empty ($_REQUEST['host_id']))
	{
		$host_id = intval ($_REQUEST['host_id']);
	}

	/* If the editor gave us the links source node name, try to find the device_id
	 * so we can present the user with the interfaces of this particular device. */
	if (isset ($_REQUEST['node1']) and !empty ($_REQUEST['node1']))
	{
		$node1 = strtolower ($_REQUEST['node1']);
		$node1_id = \App\Models\Device::where('hostname', 'like', "%$node1%")->value('device_id');
		if ($node1_id)
			$host_id = $node1_id;
	}

	 // Link query
     $hosts = \App\Models\Device::orderBy('hostname')->get(['device_id', 'hostname']);
?>

<h3>Pick a data source:</h3>

<form name="mini">
<?php 
if($hosts->isNotEmpty()) {
	print 'Host: <select name="host_id"  onChange="applyDSFilterChange(document.mini)">';

	print '<option '.($host_id==-1 ? 'SELECTED' : '' ).' value="-1">Any</option>';
	print '<option '.($host_id==0 ? 'SELECTED' : '' ).' value="0">None</option>';
	foreach ($hosts as $host)
	{
		print '<option ';
		if($host_id==$host['device_id']) print " SELECTED ";
		print 'value="'.$host['device_id'].'">'.$host['hostname'].'</option>';
	}
	print '</select><br />';
}

	print '<span class="filter" style="display: none;">Filter: <input id="filterstring" name="filterstring" size="20"> (case-sensitive)<br /></span>';
	print '<input id="overlib" name="overlib" type="checkbox" value="yes" '.($overlib ? 'CHECKED' : '' ).'> <label for="overlib">Also set OVERLIBGRAPH and INFOURL.</label><br />';
	print '<input id="aggregate" name="aggregate" type="checkbox" value="yes" '.($aggregate ? 'CHECKED' : '' ).'> <label for="aggregate">Append TARGET to existing one (Aggregate)</label><br />';
	print '<span class="ignore"><input id="ignore_desc" name="ignore_desc" type="checkbox" value="yes"> <label for="ignore_desc">Ignore blank interface descriptions</label></span>';

	print '</form><div class="listcontainer"><ul id="dslist">';

	/*
	 * Query interfaces (if we should)...
	 */
	$result = Null;
	if ($host_id != 0) {
	    $devices = \App\Models\Device::when($host_id > 0, function ($query) use ($host_id) {
	        $query->where('device_id', $host_id);
        })
        ->with(['ports' => function ($query) use ($weathermap_config) {
            $query->orderBy($weathermap_config['sort_if_by']);
        }])
        ->orderBy('hostname')
        ->get();
	}

	$i=0;
    if ($devices->isNotEmpty()) {
        foreach ($devices as $device) {
            if (!is_null($device->ports)) {
                foreach ($device->ports as $port) {
                    echo "<li class=\"row" . ($i % 2) . "\">";
                    $key = $device->device_id . "','" . $device->hostname . "','" . $port->port_id . "','" . addslashes($port->ifAlias) . "','" . addslashes($port->ifDescr) . "','" . (int)$port->ifIndex;

                    echo "<a href=\"#\" onclick=\"update_source_step1('$key')\">" . $device->displayName() . "/$port->ifDescr Desc: $port->ifAlias</a>";
                    echo "</li>\n";
                }
                $i++;
            }

        }
    } else {
		print "<li>No results...</li>";
	}

?>
</ul>
</div>
</body>
</html>
<?php
} // end of link step 1

if(isset($_REQUEST['command']) && $_REQUEST["command"]=='node_step1')
{
	$host_id = -1;

	
	$overlib = true;
	$aggregate = false;
	
	if(isset($_REQUEST['aggregate'])) $aggregate = ( $_REQUEST['aggregate']==0 ? false : true);
	if(isset($_REQUEST['overlib'])) $overlib= ( $_REQUEST['overlib']==0 ? false : true);
	
	
	if(isset($_REQUEST['host_id']))
	{
		$host_id = intval($_REQUEST['host_id']);
	}

	 $hosts = \App\Models\Device::orderBy('hostname')->get(['device_id AS id', 'hostname AS name']);

?>
<html>
<head>
<script type="text/javascript" src="vendor/jquery/dist/jquery.min.js"></script>
<script type="text/javascript">

	function filterlist(previous)
	{
		var filterstring = $('input#filterstring').val();	
		
		if(filterstring=='')
		{
			$('ul#dslist > li').show();
			return;
		}
		
		if(filterstring!=previous)
		{	
				$('ul#dslist > li').hide();
				$("ul#dslist > li:contains('" + filterstring + "')").show();
				//$('ul#dslist > li').contains(filterstring).show();
		}
	}

	$(document).ready( function() {
		$('span.filter').keyup(function() {
			var previous = $('input#filterstring').val();
			setTimeout(function () {filterlist(previous)}, 500);
		}).show();
	});

	function applyDSFilterChange(objForm) {
                strURL = '?host_id=' + objForm.host_id.value;
                strURL = strURL + '&command=node_step1';
				if( objForm.overlib.checked)
				{
					strURL = strURL + "&overlib=1";
				}
				else
				{
					strURL = strURL + "&overlib=0";
				}
				
				//if( objForm.aggregate.checked)
				//{
				//	strURL = strURL + "&aggregate=1";
				//}
				//else
				//{
				//	strURL = strURL + "&aggregate=0";
				//}
                document.location = strURL;
        }
	
	</script>
	<script type="text/javascript">

	function update_source_step1(graphid,name)
	{
		// This is the section that sets the Node Properties
		var graph_url, hover_url;

		var base_url = '<?php echo isset($config['base_url'])?$config['base_url']:''; ?>';

		if (typeof window.opener == "object") {

			graph_url = base_url + 'graph.php?height=100&width=512&device=' + graphid + '&type=device_bits&legend=no';
			info_url = base_url + 'device/device=' + graphid +'/';

			// only set the overlib URL unless the box is checked
			if( document.forms['mini'].overlib.checked)
			{
				opener.document.forms["frmMain"].node_infourl.value = info_url;
			}
			opener.document.forms["frmMain"].node_hover.value = graph_url;
                        opener.document.forms["frmMain"].node_new_name.value = graphid;
                        opener.document.forms["frmMain"].node_label.value = name;
		}
		self.close();		
	}
	</script>
<style type="text/css">
	body { font-family: sans-serif; font-size: 10pt; }
	ul { list-style: none;  margin: 0; padding: 0; }
	ul { border: 1px solid black; }
	ul li.row0 { background: #ddd;}
	ul li.row1 { background: #ccc;}
	ul li { border-bottom: 1px solid #aaa; border-top: 1px solid #eee; padding: 2px;}
	ul li a { text-decoration: none; color: black; }
</style>
<title>Pick a graph</title>
</head>
<body>

<h3>Pick a graph:</h3>

<form name="mini">
<?php 
if($hosts->isNotEmpty()) {
	print 'Host: <select name="host_id"  onChange="applyDSFilterChange(document.mini)">';

	print '<option '.($host_id==-1 ? 'SELECTED' : '' ).' value="-1">Any</option>';
	print '<option '.($host_id==0 ? 'SELECTED' : '' ).' value="0">None</option>';
	foreach ($hosts as $host)
	{
		print '<option ';
		if($host_id==$host['id']) print " SELECTED ";
		print 'value="'.$host['id'].'">'.$host['name'].'</option>';
	}
	print '</select><br />';
}

	print '<span class="filter" style="display: none;">Filter: <input id="filterstring" name="filterstring" size="20"> (case-sensitive)<br /></span>';
	print '<input id="overlib" name="overlib" type="checkbox" value="yes" '.($overlib ? 'CHECKED' : '' ).'> <label for="overlib">Set both OVERLIBGRAPH and INFOURL.</label><br />';

	print '</form><div class="listcontainer"><ul id="dslist">';

	if($hosts->isNotEmpty())
	{
		$i=0;
		foreach($hosts as $host) {
			echo "<li class=\"row".($i%2)."\">";
			$key = $host['id'];
			$name = $host['name'];
			echo "<a href=\"#\" onclick=\"update_source_step1('$key','$name')\">". $host['name'] . "</a>";
			echo "</li>\n";
			$i++;
		}
	}
	else
	{
		print "No results...";
	}

?>
</ul>
</body>
</html>
<?php
} // end of node step 1

// vim:ts=4:sw=4:
?>
