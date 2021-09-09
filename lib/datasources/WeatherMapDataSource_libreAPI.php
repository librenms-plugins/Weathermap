<?php
// Pluggable datasource for PHP Weathermap 0.9
// - Query Librenms API for device status allowing for scale to set color of node

// !! must have jq & curl installed !! 

// in top of map .conf do:

// SCALE updown 0 0  255 0 0
// SCALE updown 0.5 1 255 255 255

//  on node do: 

//  USESCALE updown out
//  TARGET libreAPI:hostname

//  not sure howto pull those values from the actualy librenms config.php so set the env below $weatherapikey $librenmsurl 

class WeatherMapDataSource_libreAPI extends WeatherMapDataSource {

	function Init(&$map)
	{
		$this->curl_cmd = "/usr/bin/curl";
		return(TRUE);
	}
	function Recognise($targetstring)
	{
		if(preg_match("/^libreAPI:(\S+)$/",$targetstring,$matches))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function ReadData($targetstring, &$map, &$item)
	{
		//created and set via http://librenms/api-access
		$weatherapikey = "a3449b6f91cdd76400d0118ba3e8cf12";
		$librenmsurl = "http://librenms";
		//set the above to match your env
		$data[IN] = NULL;
		$data[OUT] = NULL;
		if(preg_match("/^libreAPI:(\S+)$/",$targetstring,$matches))
		{
			$target = $matches[1];
			if(is_executable($this->curl_cmd))
			{
				$command = $this->curl_cmd." -s -H X-Auth-Token:$weatherapikey $librenmsurl/api/v0/devices/$target | jq '.devices[].status'";
				wm_debug("Running $command\n");
				$pipe=popen($command, "r");
				echo "'$pipe'; " . gettype($pipe) . "\n";
				$data[OUT] = fread($pipe, 2096);
				echo $pipe;
				pclose($pipe);
				//convert the true / flase into int for scale
				$data[OUT] = (int) filter_var($data[OUT], FILTER_VALIDATE_BOOLEAN);
				}

			}
			wm_debug ("ReadData: Returning = ".($data[OUT]===NULL?'NULL':$data[OUT])."\n");
			return( array($data[IN], $data[OUT]) );
		}
	}


// vim:ts=4:sw=4:
