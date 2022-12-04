<?php
// RRDtool datasource plugin.
//     gauge:filename.rrd:ds_in:ds_out
//     filename.rrd:ds_in:ds_out
//     filename.rrd:ds_in:ds_out

include_once(dirname(__FILE__)."/../ds-common.php");
include_once(dirname(__FILE__)."/../database.php");

class WeatherMapDataSource_rrd extends WeatherMapDataSource 
{

    function Init(&$map)
    {
        global $config;

        if (file_exists($map->rrdtool))
        {
            if((function_exists('is_executable')) && (!is_executable($map->rrdtool)))
            {
                wm_warn("RRD DS: RRDTool exists but is not executable? [WMRRD01]\n");
                return(FALSE);
            }
        $map->rrdtool_check="FOUND";
        
        return(TRUE);
        }
        
        // normally, DS plugins shouldn't really pollute the logs
        // this particular one is important to most users though...
        if($map->context=='cli')
        {
            wm_warn("RRD DS: Can't find RRDTOOL. Check line 29 of the 'weathermap' script.\nRRD-based TARGETs will fail. [WMRRD02]\n");
        }
        
        if($map->context=='cacti')
        {    // unlikely to ever occur
            wm_warn("RRD DS: Can't find RRDTOOL. Check your Cacti config. [WMRRD03]\n");
        }

        return(FALSE);
    }

    function Recognise($targetstring)
    {
        if(preg_match("/^(.*\.rrd):([\-a-zA-Z0-9_]+):([\-a-zA-Z0-9_]+)$/",$targetstring,$matches))
        {
            return TRUE;
        }
        elseif(preg_match("/^(.*\.rrd)$/",$targetstring,$matches))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    function wmrrd_read_from_real_rrdtool_aggregate($rrdfile,$cf,$aggregatefn,$start,$end,$dsnames, &$data, &$map, &$data_time,&$item)
    {

        wm_debug("RRD ReadData: VDEF style, for ".$item->my_type()." ".$item->name."\n");

        $extra_options = $map->get_hint("rrd_options");

        // rrdcached Support: strip "./" from Data Source
        if ($map->daemon)
        {
            if (substr($rrdfile, 0, 2) == "./")
                $rrdfile = substr($rrdfile, 2);
        }

        // Assemble an array of command args.
        // In a real programming language, we'd be able to pass this directly to exec()
        // However, this will at least allow us to put quotes around args that need them
        $args = array();
        $args[] = "graph";
        $args[] = "/dev/null";
        $args[] = "-f";
        $args[] = "''";
        $args[] = "--start";
        $args[] = $start;
        $args[] = "--end";
        $args[] = $end;

        // rrdcached Support: Use daemon
        if ($map->daemon)
        {
            $args[] = "--daemon";
            $args[] = $map->daemon_args;
        }

        // assemble an appropriate RRDtool command line, skipping any '-' DS names.
        // $command = $map->rrdtool . " graph /dev/null -f ''  --start $start --end $end ";
        if($dsnames[IN] != '-')
        {
            # $command .= "DEF:in=$rrdfile:".$dsnames[IN].":$cf ";
            # $command .= "VDEF:agg_in=in,$aggregatefn ";
            # $command .= "PRINT:agg_in:'IN %lf' ";

            $args[] = "DEF:in=$rrdfile:".$dsnames[IN].":$cf";
            $args[] = "VDEF:agg_in=in,$aggregatefn";
            $args[] = "PRINT:agg_in:'IN %lf'";
        }

        if($dsnames[OUT] != '-')
        {
            # $command .= "DEF:out=$rrdfile:".$dsnames[OUT].":$cf ";
            # $command .= "VDEF:agg_out=out,$aggregatefn ";
            # $command .= "PRINT:agg_out:'OUT %lf' ";

            $args[] = "DEF:out=$rrdfile:".$dsnames[OUT].":$cf";
            $args[] = "VDEF:agg_out=out,$aggregatefn";
            $args[] = "PRINT:agg_out:'OUT %lf'";
        }

        $command = $map->rrdtool;

        foreach ($args as $arg)
        {
            if(strchr($arg," ") != FALSE)
            {
                $command .= ' "' . $arg . '"';
            }
            else
            {
                $command .= ' ' . $arg;
            }
        }

        $command .= " " . $extra_options;

        wm_debug("RRD ReadData: Running: $command\n");
        $pipe=popen($command, "r");

        $lines=array ();
        $count = 0;
        $linecount = 0;

        if (isset($pipe))
        {
            // fgets($pipe, 4096); // skip the blank line
            $buffer='';
            $data_ok = FALSE;

            while (!feof($pipe))
            {
                $line=fgets($pipe, 4096);

                // there might (pre-1.5) or might not (1.5+) be a leading blank line
                // we don't want to count it if there is
                if (trim($line) != "") 
                {
                    wm_debug("> " . $line);
                    $buffer .= $line;
                    $lines[] = $line;
                    $linecount++;
                }
            }

            pclose ($pipe);

            if($linecount>1)
            {
                foreach ($lines as $line)
                {
                    if(preg_match('/^\'(IN|OUT)\s(\-?\d+[\.,]?\d*e?[+-]?\d*:?)\'$/i', $line, $matches))
                    {
                        wm_debug("MATCHED: ".$matches[1]." ".$matches[2]."\n");
                        if($matches[1]=='IN') $data[IN] = floatval($matches[2]);
                        if($matches[1]=='OUT') $data[OUT] = floatval($matches[2]);
                        $data_ok = TRUE;
                    }
                }
                
                if($data_ok)
                {
                    if($data[IN] === NULL) $data[IN] = 0;
                    if($data[OUT] === NULL) $data[OUT] = 0;
                }
            }
            else
            {
                wm_warn("Not enough output from RRDTool. [WMRRD09]\n");
            }
        }
        else
        {
            $error = error_get_last();
            wm_warn("RRD ReadData: failed to open pipe to RRDTool: ". $error['message']." [WMRRD04]\n");
        }
        
        wm_debug ("RRD ReadDataFromRealRRDAggregate: Returning (".($data[IN]===NULL?'NULL':$data[IN]).",".($data[OUT]===NULL?'NULL':$data[OUT]).",$data_time)\n");
    }

    function wmrrd_read_from_real_rrdtool($rrdfile,$cf,$start,$end,$dsnames, &$data, &$map, &$data_time,&$item)
    {
        wm_debug("RRD ReadData: traditional style\n");

        // we get the last 800 seconds of data - this might be 1 or 2 lines, depending on when in the
        // cacti polling cycle we get run. This ought to stop the 'some lines are grey' problem that some
        // people were seeing

        $extra_options = $map->get_hint("rrd_options");

        $values = array();
        $args = array();
        
        // rrdcached Support: strip "./" from Data Source
        if ($map->daemon)
        {
            if (substr($rrdfile, 0, 2) == "./")
                $rrdfile = substr($rrdfile, 2);
        }
        
        $args[] = "fetch";
        $args[] = $rrdfile;
        $args[] = $cf;
        $args[] = "--start";
        $args[] = $start;
        $args[] = "--end";
        $args[] = $end;

        // rrdcached Support: Use daemon
        if ($map->daemon)
        {
            $args[] = "--daemon";
            $args[] = $map->daemon_args;
        }

        $command = $map->rrdtool;

        foreach ($args as $arg)
        {
            if(strchr($arg," ") != FALSE)
            {
                $command .= ' "' . $arg . '"';
            }
            else
            {
                $command .= ' ' . $arg;
            }
        }
        $command .= " " . $extra_options;

        wm_debug ("RRD ReadData: Running: $command\n");

        $pipe=popen($command, "r");

        $lines=array ();
        $count = 0;
        $linecount = 0;

        if (isset($pipe))
        {
            $headings=fgets($pipe, 4096);
           
            // this replace fudges 1.2.x output to look like 1.0.x
            // then we can treat them both the same.
            $heads=preg_split("/\s+/", preg_replace("/^\s+/","timestamp ",$headings) );

            //fgets($pipe, 4096); // skip the blank line
            $buffer='';

            while (!feof($pipe))
            {
                $line=fgets($pipe, 4096);
                // there might (pre-1.5) or might not (1.5+) be a leading blank line
                // we don't want to count it if there is
                if (trim($line) != "") 
                {
                    wm_debug("> " . $line);
                    $buffer .= $line;
                    $lines[] = $line;
                    $linecount++;
                }

            }
            pclose ($pipe);

            wm_debug("RRD ReadData: Read $linecount lines from rrdtool\n");
            wm_debug("RRD ReadData: Headings are: $headings\n");

            if( (in_array($dsnames[IN],$heads) || $dsnames[IN] == '-') && (in_array($dsnames[OUT],$heads) || $dsnames[OUT] == '-') )
            {
                // deal with the data, starting with the last line of output
                $rlines=array_reverse($lines);

                foreach ($rlines as $line)
                {
                    wm_debug ("--" . $line . "\n");
                    $cols=preg_split("/\s+/", $line);
                    for ($i=0, $cnt=count($cols)-1; $i < $cnt; $i++)
                    {
                        $h = $heads[$i];
                        $v = $cols[$i];
                        # print "|$h|,|$v|\n";
                        $values[$h] = trim($v);
                    }

                    $data_ok=FALSE;

                    foreach (array(IN,OUT) as $dir)
                    {
                        $n = $dsnames[$dir];
                        // print "|$n|\n";
                        if(array_key_exists($n,$values))
                        {
                            $candidate = $values[$n];
                            if(preg_match('/^\-?\d+[\.,]?\d*e?[+-]?\d*:?$/i', $candidate))
                            {
                                $data[$dir] = $candidate;
                                wm_debug("$candidate is OK value for $n\n");
                                $data_ok = TRUE;
                            }
                        }
                    }

                    if($data_ok)
                    {
                        // at least one of the named DS had good data
                        $data_time = intval($values['timestamp']);

                        // 'fix' a -1 value to 0, so the whole thing is valid
                        // (this needs a proper fix!)
                        if($data[IN] === NULL) $data[IN] = 0;
                        if($data[OUT] === NULL) $data[OUT] = 0;

                        // break out of the loop here
                        break;
                    }
                 }
            }
            else
            {
                // report DS name error
                $names = join(",",$heads);
                $names = str_replace("timestamp,","",$names);
                wm_warn("RRD ReadData: At least one of your DS names (".$dsnames[IN]." and ".$dsnames[OUT].") were not found, even though there was a valid data line. Maybe they are wrong? Valid DS names in this file are: $names [WMRRD06]\n");
            }

        }
        else
        {
            $error = error_get_last();
            wm_warn("RRD ReadData: failed to open pipe to RRDTool: ". $error['message']." [WMRRD04]\n");
        }
        wm_debug ("RRD ReadDataFromRealRRD: Returning (".($data[IN]===NULL?'NULL':$data[IN]).",".($data[OUT]===NULL?'NULL':$data[OUT]).",$data_time)\n");
    }

    // Read from a data source, and return a 3-part array (invalue, outvalue and datavalid time_t)
    // invalue and outvalue should be -1 if there is no valid data.
    // data_time is intended to allow more informed graphing in the future
    function ReadData($targetstring, &$map, &$item)
    {
        global $config;

        $dsnames[IN] = "traffic_in";
        $dsnames[OUT] = "traffic_out";
        $data[IN] = NULL;
        $data[OUT] = NULL;
        $SQL[IN] = 'select null';
        $SQL[OUT] = 'select null';
        $rrdfile = $targetstring;

        if($map->get_hint("rrd_default_in_ds") != '') 
        {
            $dsnames[IN] = $map->get_hint("rrd_default_in_ds");
            wm_debug("Default 'in' DS name changed to ".$dsnames[IN].".\n");
        }
        
        if($map->get_hint("rrd_default_out_ds") != '') 
        {
            $dsnames[OUT] = $map->get_hint("rrd_default_out_ds");
            wm_debug("Default 'out' DS name changed to ".$dsnames[OUT].".\n");
        }

        $multiplier = 8; // default bytes-to-bits

        $inbw = NULL;
        $outbw = NULL;
        $data_time = 0;

        if(preg_match("/^(.*\.rrd):([\-a-zA-Z0-9_]+):([\-a-zA-Z0-9_]+)$/",$targetstring,$matches))
        {
            $rrdfile = $matches[1];
            $dsnames[IN] = $matches[2];
            $dsnames[OUT] = $matches[3];

            wm_debug("Special DS names seen (".$dsnames[IN]." and ".$dsnames[OUT].").\n");
        }

        if(preg_match("/^rrd:(.*)/",$rrdfile,$matches))
        {
            $rrdfile = $matches[1];
        }

        if(preg_match("/^gauge:(.*)/",$rrdfile,$matches))
        {
            $rrdfile = $matches[1];
            $multiplier = 1;
        }

        if(preg_match("/^scale:([+-]?\d*\.?\d*):(.*)/",$rrdfile,$matches))
        {
                $rrdfile = $matches[2];
                $multiplier = $matches[1];
        }

        wm_debug("SCALING result by $multiplier\n");

        // try and make a complete path, if we've been given a clue
        // (if the path starts with a . or a / then assume the user knows what they are doing)
        // Check for the cases where there is an accidental . in the target
        if(!preg_match("/^(\/)/",$rrdfile))
        {
            $fileExist = file_exists($rrdfile);
            
            if (!$fileExist) 
            {
                $rrdfile_tmp = $rrdfile;
                if(preg_match("/^(\.)/",$rrdfile))
                {
                    $rrdfile_tmp = substr($rrdfile, 1);
                    if(preg_match("/^(\/)/",$rrdfile_tmp))
                    {
                        $rrdfile_tmp = substr($rrdfile_tmp, 1);
                    }
                }

                $fileExist = file_exists($rrdfile_tmp);
                if(!$fileExist) 
                {
                    $rrdbase = $map->get_hint('rrd_default_path');
                    if ($rrdbase != '') 
                    {
                        $rrdfile = $rrdbase . "/" . $rrdfile_tmp;
                    }
                } 
                else
                {
                    $rrdfile = $rrdfile_tmp;
                }
            }
        }

        $cfname = $map->get_hint('rrd_cf');
        if($cfname=='') $cfname='AVERAGE';

        $period = intval($map->get_hint('rrd_period'));
        if($period == 0) $period = 800;
        
        $start = $map->get_hint('rrd_start');
        if($start == '') 
        {
            $start = "now-$period";
            $end = "now";
        }
        else
        {
            $end = "start+".$period;
        }

        $use_poller_output = intval($map->get_hint('rrd_use_poller_output'));
        $nowarn_po_agg = intval($map->get_hint("nowarn_rrd_poller_output_aggregation"));
        $aggregatefunction = $map->get_hint('rrd_aggregate_function');

        if($aggregatefunction != '' && $use_poller_output==1)
        {
            $use_poller_output=0;
            if($nowarn_po_agg==0)
            {
                wm_warn("Can't use poller_output for rrd-aggregated data - disabling rrd_use_poller_output [WMRRD10]\n");
            }
        }

        if($use_poller_output == 1)
        {
            wm_debug("Going to try poller_output, as requested.\n");
            WeatherMapDataSource_rrd::wmrrd_read_from_poller_output($rrdfile,"AVERAGE",$start,$end, $dsnames, $data,$map, $data_time,$item);
        }

        // if poller_output didn't get anything, or if it couldn't/didn't run, do it the old-fashioned way
        // - this will still be the case for the first couple of runs after enabling poller_output support
        //   because there won't be valid data in the weathermap_data table yet.
        if( ($dsnames[IN]!='-' && $data[IN] === NULL) || ($dsnames[OUT] !='-' && $data[OUT] === NULL) )
        {
            if($aggregatefunction != '')
            {
                WeatherMapDataSource_rrd::wmrrd_read_from_real_rrdtool_aggregate($rrdfile,$cfname,$aggregatefunction, $start,$end, $dsnames, $data,$map, $data_time,$item);
            }
            else
            {
                // do this the tried and trusted old-fashioned way
                WeatherMapDataSource_rrd::wmrrd_read_from_real_rrdtool($rrdfile,$cfname,$start,$end, $dsnames, $data,$map, $data_time,$item);
            }
        }
        else
        {
            wm_warn ("Target $rrdfile doesn't exist. Is it a file? [WMRRD06]\n");
        }
    
        // if the Locale says that , is the decimal point, then rrdtool
        // will honour it. However, floatval() doesn't, so let's replace
        // any , with . (there are never thousands separators, luckily)
        if($data[IN] !== NULL)
        {
            $data[IN] = floatval(str_replace(",",".",$data[IN]));
            $data[IN] = $data[IN] * $multiplier;
        }
        
        if($data[OUT] !== NULL)
        {
            $data[OUT] = floatval(str_replace(",",".",$data[OUT]));
            $data[OUT] = $data[OUT] * $multiplier;
        }

        wm_debug ("RRD ReadData: Returning (".($data[IN]===NULL?'NULL':$data[IN]).",".($data[OUT]===NULL?'NULL':$data[OUT]).",$data_time)\n");

        return( array($data[IN], $data[OUT], $data_time) );
    }
}

// vim:ts=4:sw=4:
