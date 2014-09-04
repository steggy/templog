<?
global $inifile;
global $ini_array;
global $temperature;


$GLOBALS['inifile'] = '/var/www/webtemp/tempset.ini';
readini($GLOBALS['inifile']);
gettemp();

if(isset($_POST['w']))
{
	
    switch(strtolower($_POST['w']))
	{
		case "update":
			update();
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
	<title>Tempeture</title>
    <style>
        body {
        font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
        /*font-size: 62.5%;*/
        background-color:#FBF7C2;
        }

        #info
        {
        position:fixed;
        top:63px;
        left:33px;
        width: 330px;
        height: 400px;
        /*display:none;*/
        background-color:cyan;
        border: 1px solid #5C755E;
        border-radius: 6px;
        box-shadow: 5px 5px 3px #888;
        font-size:20px;
        }
        input[type=submit] 
        {
        border: 1px solid #5C755E;
        border-radius: 6px;
        box-shadow: 5px 5px 3px #888;
        }

        button
        {
        border: 1px solid #5C755E;
        border-radius: 6px;
        box-shadow: 5px 5px 3px #888;
        font-size:20px;
        }
        
        </style>
</head>
<body>

<div id=info>
	<form action="<?=$_SERVER['REQUEST_URI'];?>" method="post">
    <table>
       <tr><td>Unit name:</td><td><?=$GLOBALS['ini_array']['unit']['unitname'];?></td></tr>
	   <tr><td>Current Temp C:</td><td><?=$GLOBALS['temperature'];?></td></tr>
	   <tr><td>Sample Rate Sec.: </td><td><input size=2 name="rate" value="<?=$GLOBALS['ini_array']['sensor']['sample_rate'];?>"></td></tr>
	   <tr><td>Sensor Name: </td><td><input size=8 name="sensor" value="<?=$GLOBALS['ini_array']['sensor']['name'];?>"></td></tr>
       <tr><td>Server: </td><td><input size=8 name="server" value="<?=$GLOBALS['ini_array']['database']['dbhost'];?>"></td></tr>
	<tr><td colspan=3><input name="w" type=submit value="UPDATE"></form></td></tr>
</table>
    <br>
    Last Updated <?= date ("m-d-Y H:i:s.", filemtime($GLOBALS['inifile']));?>
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
//##########################################################################
function update()
{
$GLOBALS['ini_array'] = parse_ini_file($GLOBALS['inifile'],true);

$GLOBALS['ini_array']['sensor']['sample_rate'] = $_POST['rate'];
$GLOBALS['ini_array']['sensor']['name'] = $_POST['sensor'];
$GLOBALS['ini_array']['database']['dbhost'] = $_POST['server'];

write_ini_file($GLOBALS['ini_array'], $GLOBALS['inifile']);
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