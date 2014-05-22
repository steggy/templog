<?
global $inifile;
global $ini_array;
global $temperature;


$GLOBALS['inifile'] = '/var/www/tempset.ini';
readini($GLOBALS['inifile']);
gettemp();

if(isset($_POST['w']))
{
	switch(strtolower($_POST['w']))
	{
		case "update":
			//update();
			first();
			break;
	}
}else{
	first();
}



?>

<?
//##########################################################################
function first()
{
?>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="temp.css" />
</head>
<body>
hello
<div id=info>
	<br>
	Unit name: <?=$GLOBALS['ini_array']['unit']['unitname']?>
	<br>
	Current Temp C: <?=$GLOBALS['temperature']?>
	<br>
	Sample Rate Sec.: <input size=2 name=rate value="<?=$GLOBALS['ini_array']['sensor']['sample_rate']?>">
	<br>
	Sensor Name: <input size=8 name=rate value="<?=$GLOBALS['ini_array']['sensor']['name']?>">
	<br>
	<input type=submit value="UPDATE">
</div>
</body>
</html>
<?
}
//##########################################################################
?>


<?
//##########################################################################
function readini($file)
{
$GLOBALS['ini_array'] = parse_ini_file($file,true);



/*$GLOBALS['dbusername'] = $ini_array['database']['dbusername'];
$GLOBALS['dbpassword'] = $ini_array['database']['dbpassword'];
$GLOBALS['database'] = $ini_array['database']['database'];
$GLOBALS['dbhost'] = $ini_array['database']['dbhost'];
$GLOBALS['sname'] = $ini_array['sensor']['name'];
$GLOBALS['samprate'] = $ini_array['sensor']['sample_rate'];*/

}
//###############################################################################
?>

<?
//'*******************************************************************************
function write_ini_file($assoc_arr, $path, $has_sections=TRUE) { 
    $content = ""; 
    if ($has_sections) { 
        foreach ($assoc_arr as $key=>$elem) { 
            $content .= "[".$key."]\n"; 
            foreach ($elem as $key2=>$elem2) { 
                if(is_array($elem2)) 
                { 
                    for($i=0;$i<count($elem2);$i++) 
                    { 
                        $content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
                    } 
                } 
                else if($elem2=="") $content .= $key2." = \n"; 
                else $content .= $key2." = \"".$elem2."\"\n"; 
            } 
        } 
    } 
    else { 
        foreach ($assoc_arr as $key=>$elem) { 
            if(is_array($elem)) 
            { 
                for($i=0;$i<count($elem);$i++) 
                { 
                    $content .= $key2."[] = \"".$elem[$i]."\"\n"; 
                } 
            } 
            else if($elem=="") $content .= $key2." = \n"; 
            else $content .= $key2." = \"".$elem."\"\n"; 
        } 
    } 

    if (!$handle = fopen($path, 'w')) { 
	fclose($handle); 
        return false; 
    } 
    if (!fwrite($handle, $content)) { 
	fclose($handle);         
	return false; 
    } 
	fclose($handle); 
	return true; 
}
//'*******************************************************************************
?>



<?
//###############################################################################
function gettemp()
{
if (!defined("THERMOMETER_SENSOR_PATH")) define("THERMOMETER_SENSOR_PATH", "/sys/bus/w1/devices/" .$GLOBALS['ini_array']['sensor']['name'] ."/w1_slave"); 
// Open resource file for thermometer
$thermometer = fopen(THERMOMETER_SENSOR_PATH, "r"); 
// Get the contents of the resource
$thermometerReadings = fread($thermometer, filesize(THERMOMETER_SENSOR_PATH)); 
// Close resource file for thermometer
fclose($thermometer); 
// We're only interested in the 2nd line, and the value after the t= on the 2nd line
//echo "Steggy\n" .$thermometerReadings ."\n";

$matches = explode("\n",$thermometerReadings);
//echo $matches[1] ."\n";
//echo substr($matches[1],strpos($matches[1],"t=") +2) /1000 ."\n";
$GLOBALS['temperature'] = substr($matches[1],strpos($matches[1],"t=") +2) / 1000;
}
//###############################################################################
?>

<?
/*[database]
dbusername="templog"
dbpassword="templog123"
database="templogger"
dbhost = "10.3.101.219"

[sensor]
name = '28-00000574cbe8'
;in seconds
sample_rate = 60

[unit]
unitname = "bbb-ubuntu12-02"*/
?>