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
// 	USESCALE updown out
//  TARGET libreAPI:hostname
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

    function ReadData($targetstring, &$map, &$item)
    {
        //created and set via http://librenms/api-access
        $weatherapikey = "a3449b6f91cdd76400d0118ba3e8cf12";
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
                $response = json_decode($response);
                $item->add_hint("libreAPI_version", $response->devices[0]
                    ->version);
                $item->add_hint("libreAPI_IP", $response->devices[0]
                    ->ip);
                $item->add_hint("libreAPI_sysDescr", $response->devices[0]
                    ->sysDescr);
                $item->add_hint("libreAPI_DUMP", json_encode($response));
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
