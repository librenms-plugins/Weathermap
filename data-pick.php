<?php

// ******************************************
// sensible defaults
$mapdir='configs';
$ignore_librenms=FALSE;
$config['base_url'] = '/';
$whats_installed = '';

$config_file_path = Null;
$librenms_base = '../../../';

// Valid config.php paths
$config_file_paths = array (
	'/etc/librenms/config.php',
	'/opt/librenms/config.php',
	'../../../config.php',
);

// Try to find most appropriate librenms config file
foreach ($config_file_paths as $path) {
	if (file_exists ($path)) {
		$config_file_path = $path;
		break;
	}
}

// check if the goalposts have moved
if (file_exists ($config_file_path)) {
	/*
	 * Include the LibreNMS config, so we know about the database.
	 *
	 * Include config first to get install dir, then load defaults and config
	 * again to get full set of config values.
	 */
	require ($config_file_path);
	$librenms_base = $config['install_dir'];
	include_once ("$librenms_base/includes/defaults.inc.php");
	require ($config_file_path);

	// FIXME: Why is this neccessary?!
	chdir ("$librenms_base/html");

	include_once("$librenms_base/includes/definitions.inc.php");
	include_once("$librenms_base/includes/functions.php");
	include_once("$librenms_base/html/includes/functions.inc.php");
	require_once("$librenms_base/html/includes/authenticate.inc.php");
	if (empty($_SESSION['authenticated']) || !isset($_SESSION['authenticated'])) {
		header('Location: /');
	}

	chdir('plugins/Weathermap');
	$librenms_found = TRUE;
}
else {
	$librenms_found = FALSE;
}

$link = mysql_connect($config['db_host'],$config['db_user'],$config['db_pass'])
                or die('Could not connect: ' . mysql_error());
mysql_selectdb($config['db_name'],$link) or die('Could not select database: '.mysql_error());


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

	//$SQL_graphid = sprintf("SELECT graph_templates_item.local_graph_id, title_cache FROM graph_templates_item,graph_templates_graph,data_template_rrd where graph_templates_graph.local_graph_id = graph_templates_item.local_graph_id  and task_item_id=data_template_rrd.id and local_data_id=%d LIMIT 1;",$dataid);

	//mysql_selectdb($config['db_name'],$link) or die('Could not select database: '.mysql_error());

	//$result = mysql_query($SQL_graphid) or die('Query failed: ' . mysql_error());
	//$line = mysql_fetch_array($result, MYSQL_ASSOC);
	//$graphid = $line['local_graph_id'];

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

	window.onload = update_source_step2(<?php echo $graphid ?>);

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
	<script type="text/javascript" src="editor-resources/jquery-latest.min.js"></script>
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

		var rra_path = <?php echo js_escape('./'); ?>+name+'/port-';

		if (typeof window.opener == "object") {
			fullpath = rra_path+ifIndex+'.rrd:INOCTETS:OUTOCTETS';
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

	//$SQL_picklist = "select data_local.host_id, data_template_data.local_data_id, data_template_data.name_cache, data_template_data.active, data_template_data.data_source_path from data_local,data_template_data,data_input,data_template where data_local.id=data_template_data.local_data_id and data_input.id=data_template_data.data_input_id and data_local.data_template_id=data_template.id ";

	$host_id = -1;
	
	$overlib = true;
	$aggregate = false;
	
	if(isset($_REQUEST['aggregate'])) $aggregate = ( $_REQUEST['aggregate']==0 ? false : true);
	if(isset($_REQUEST['overlib'])) $overlib= ( $_REQUEST['overlib']==0 ? false : true);
	
	
	if(isset($_REQUEST['host_id']))
	{
		$host_id = intval($_REQUEST['host_id']);
		//if($host_id>=0) $SQL_picklist .= " and data_local.host_id=$host_id ";
	}
	
	//$SQL_picklist .= " order by name_cache;";
	
	 // Link query
	 $result = mysql_query("SELECT device_id,hostname FROM devices ORDER BY hostname");
	 //$hosts = mysql_fetch_assoc($result);
	 //$result = mysql_query($SQL_picklist);
	 $hosts = 1;
?>

<h3>Pick a data source:</h3>

<form name="mini">
<?php 
if(sizeof($hosts) > 0) {
	print 'Host: <select name="host_id"  onChange="applyDSFilterChange(document.mini)">';

	print '<option '.($host_id==-1 ? 'SELECTED' : '' ).' value="-1">Any</option>';
	print '<option '.($host_id==0 ? 'SELECTED' : '' ).' value="0">None</option>';
	while ($host = mysql_fetch_assoc($result))
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

	$query = "SELECT devices.device_id,hostname,ports.port_id,ports.ifAlias,ports.ifIndex,ports.ifDescr,ports.deleted FROM devices LEFT JOIN ports ON devices.device_id=ports.device_id WHERE ports.disabled=0";

	if($host_id > 0) {
		$query .= " AND devices.device_id='$host_id'";
	}
	
	$query .= " ORDER BY hostname,ports.ifName";
	$result = mysql_query($query);

	// print $SQL_picklist;

	$i=0;
	if( mysql_num_rows($result) > 0 )
	{
			while ($queryrows = mysql_fetch_assoc($result)) {
			echo "<li class=\"row".($i%2)."\">";
			$key = $queryrows['device_id']."','".$queryrows['hostname']."','".$queryrows['port_id']."','".addslashes($queryrows['ifAlias'])."','".addslashes($queryrows['ifDescr'])."','".$queryrows['ifIndex'];
			// Indicated if port is marked deleted
			$deleted = $queryrows['deleted'] ? " (D)" : "";
			echo "<a href=\"#\" onclick=\"update_source_step1('$key')\">". $queryrows['hostname'] . "/" . $queryrows['ifDescr'] . " Desc:" . $queryrows['ifAlias'] . "$deleted</a>";
			echo "</li>\n";
			
			$i++;
		}
	}
	else
	{
		print "<li>No results...</li>";
	}

	// Free resultset
	//mysql_free_result($result);

	// Closing connection
	//mysql_close($link);

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
	$SQL_picklist = "SELECT `device_id` AS `id`,`hostname` AS `name` FROM devices ORDER BY hostname";
	//$SQL_picklist = "SELECT 1,2,'Test','Y','/dsad'";
	
	$overlib = true;
	$aggregate = false;
	
	if(isset($_REQUEST['aggregate'])) $aggregate = ( $_REQUEST['aggregate']==0 ? false : true);
	if(isset($_REQUEST['overlib'])) $overlib= ( $_REQUEST['overlib']==0 ? false : true);
	
	
	if(isset($_REQUEST['host_id']))
	{
		$host_id = intval($_REQUEST['host_id']);
		//if($host_id>=0) $SQL_picklist .= " and graph_local.host_id=$host_id ";
	}
	//$SQL_picklist .= " order by title_cache";	
	
	 $query = mysql_query("SELECT id,hostname AS name FROM `devices` ORDER BY hostname");
	 $hosts = mysql_fetch_assoc($query);	

?>
<html>
<head>
<script type="text/javascript" src="editor-resources/jquery-latest.min.js"></script>
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
if(sizeof($hosts) > 0) {
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
	$result = mysql_query($SQL_picklist);
	if( mysql_num_rows($result) > 0)
	{
		$i=0;
		while($queryrows = mysql_fetch_assoc($result)) {
			echo "<li class=\"row".($i%2)."\">";
			$key = $queryrows['id'];
			$name = $queryrows['name'];
			echo "<a href=\"#\" onclick=\"update_source_step1('$key','$name')\">". $queryrows['name'] . "</a>";
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
