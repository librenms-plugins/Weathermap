<?php
// Pluggable datasource for PHP Weathermap 0.9
// - Query Librenms API for device status
//
// must have jq & curl installed
// in .conf do
//
// 	SCALE updown 0 0  255 0 0
// 	SCALE updown 0.5 1 255 255 255
//
// under a node do
// 	USESCALE updown out percent
//  TARGET libreAPI:{node:this:name}
class WeatherMapDataSource_libreAPI extends WeatherMapDataSource
{

    function Init(&$map)
    {
        return (true);
    }

    function Recognise($targetstring)
    {
        if (preg_match("/^libreAPI:(\S+)$/", $targetstring, $matches))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function ConvertSectoDay($inputSeconds)
    {
      $secondsInAMinute = 60;
      $secondsInAnHour = 60 * $secondsInAMinute;
      $secondsInADay = 24 * $secondsInAnHour;

      // Extract days
      $days = floor($inputSeconds / $secondsInADay);

      // Extract hours
      $hourSeconds = $inputSeconds % $secondsInADay;
      $hours = floor($hourSeconds / $secondsInAnHour);

      // Extract minutes
      $minuteSeconds = $hourSeconds % $secondsInAnHour;
      $minutes = floor($minuteSeconds / $secondsInAMinute);

      // Extract the remaining seconds
      $remainingSeconds = $minuteSeconds % $secondsInAMinute;
      $seconds = ceil($remainingSeconds);

      // Format and return
      $timeParts = [];
      $sections = [
          'day' => (int)$days,
          'hour' => (int)$hours,
          'minute' => (int)$minutes,
          'second' => (int)$seconds,
      ];

      foreach ($sections as $name => $value){
          if ($value > 0){
              $timeParts[] = $value. ' '.$name.($value == 1 ? '' : 's');
          }
      }

      return implode(', ', $timeParts);
    }

    function ReadData($targetstring, &$map, &$item)
    {
        //created and set via http://librenms/api-access
        $weatherapikey = "d8f3e7b79d20e6f41c715e7abcfffaa5";
        $librenmsurl = "http://librenms";
        //set the above to match your env
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "X-Auth-Token:" . $weatherapikey
            )
        );
        $context = stream_context_create($opts);
        $data[IN] = NULL;
        $data[OUT] = NULL;
        if (preg_match("/^libreAPI:(\S+)$/", $targetstring, $matches))
        {
            $target = $matches[1];
            {
                $response = file_get_contents($librenmsurl . '/api/v0/devices/' . $target, false, $context);
                $ports = file_get_contents($librenmsurl . '/api/v0/devices/' . $target . "/ports/1", false, $context);
                $ports = json_decode($ports);
                $response = json_decode($response);
                $n = $response->devices[0]->uptime;
                $uptimec = $this->ConvertSectoDay($n);
                $item->add_hint("libreAPI_version", $response->devices[0]
                    ->version);
                $item->add_hint("libreAPI_IP", $response->devices[0]
                    ->ip);
                $item->add_hint("libreAPI_sysDescr", $response->devices[0]
                    ->sysDescr);
                $item->add_hint("libreAPI_hardware", $response->devices[0]
                    ->hardware);
                $item->add_hint("libreAPI_uptime", $uptimec);
                $item->add_hint("libreAPI_serial", $response->devices[0]
                    ->serial);
                $item->add_hint("libreAPI_MAC", $ports->port->ifPhysAddress);
                //$item->add_hint("libreAPI_DUMP", json_encode($response));

                $data[OUT] = (int)filter_var($response->devices[0]->status, FILTER_VALIDATE_BOOLEAN);
            }

        }
        wm_debug("ReadData: Returning = " . ($data[OUT] === NULL ? 'NULL' : $data[OUT]) . "\n");
        return (array(
            $data[IN],
            $data[OUT]
        ));
    }
}

// vim:ts=4:sw=4:
