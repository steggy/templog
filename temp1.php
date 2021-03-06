#! /usr/bin/php

<?php
//This set file required for this to run. place in same directory as script 
//require_once 'tempset.php';

global $temperature;
global $dbusername;
global $dbpassword;
global $database;
global $dbhost;
global $sname;
global $samprate;
global $unitname;
global $revmajor;
global $revminor;
global $ini_array;
$GLOBALS['revmajor']="1";
$GLOBALS['revminor']="1";
readini("/var/www/webtemp/tempset.ini");
//used for RPI
/*try
{
	shell_exec('modprobe w1-gpio');
	shell_exec('modprobe w1-therm');
} catch (Exception $e) {
	fwrite($STDERR,'Caught exception: ',  $e->getMessage(), "\n");
}*/

//set sensor name in the set.php file
//$sname ='28-00000574cbe8';



$GLOBALS['unitname'] = preg_replace("/\r|\n/", "", shell_exec("/bin/hostname"));
$log = '/var/log/templogger.log';

if(isset($argv[1]))
{
	switch($argv[1])
	{
		case '-x':
			changecolor(0,0,0);		
			exit;
			break;
		case '-s':
			if(sizeof($argv) >= 6)
			{
				strobe($argv[2],$argv[3],$argv[4],$argv[5],$argv[6]);
				//changecolor(0,0,0);		
				die;
			}else{
				showusage();
				die;
			}
			break;
		case '-r':
			main();	
			break;
		case '-c':
			if(sizeof($argv) >= 4)
			{
				changecolor($argv[2],$argv[3],$argv[4]);
			}else{
				showusage();
				die;
			}
			break;
		case '-D':
			//daemon mode
			maindaemon();
			break;
		default;
			showusage();
			die;

	}

	
}else{
	showusage();
	die;
}

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

//'*******************************************************************************
function maindaemon()
{
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen('/var/log/templogger.log', 'wb');
$STDERR = fopen('/var/log/templogerror.log', 'wb');

//fork the process to work in a daemonized environment
file_put_contents($GLOBALS['log'], "Status: starting up. \n", FILE_APPEND);
$pid = pcntl_fork();
if($pid == -1){
	file_put_contents($GLOBALS['log'], "Error: could not daemonize process.n", FILE_APPEND);
	return 1; //error
}
else if($pid){
	return 0; //success
}
else{

//the main process
while(true)
{
	gettemp();
	$tf = date('Ymd H:i:s') ."\t" .$GLOBALS['unitname'] ."\t" .$GLOBALS['temperature'];
	$tf .= "\t" .round($GLOBALS['temperature'] * 9.0 / 5.0 + 32.0,2) ."\n";
	$noteit = fopen("/root/bin/mytemp","a");
	//echo fwrite($noteit,$tf."\n");
	fwrite($noteit,$tf."\n");
	fclose($noteit);
	echo $tf;
	recordtemp($GLOBALS['temperature']);
	sleep($GLOBALS['ini_array']['sensor']['sample_rate']);
}
} //end of fork
}
//'*******************************************************************************

//'*******************************************************************************
function main()
{

//the main process
while(true)
{
	gettemp();
	$tf = date('Ymd H:i:s') ."\t" .$GLOBALS['unitname'] ."\t" .$GLOBALS['temperature'];
	$tf .= "\t" .round($GLOBALS['temperature'] * 9.0 / 5.0 + 32.0,2) ."\n";
	$noteit = fopen("/root/bin/mytemp","a");
	//echo fwrite($noteit,$tf."\n");
	fwrite($noteit,$tf."\n");
	fclose($noteit);
	echo $tf;
	recordtemp($GLOBALS['temperature']);
	sleep($GLOBALS['ini_array']['sensor']['sample_rate']);
}

}
//'*******************************************************************************

//'*******************************************************************************
function recordtemp($tmp)
{
$ip = getip();
//echo "In record\n";
$tf=round($tmp * 9.0 / 5.0 + 32.0,2);
$query = "insert into tlog (ip,c,f,ldte,unit) values ('" .$ip ."','" .$tmp ."','" .$tf ."','" .date('Y-m-d H:i:s') ."','" .$GLOBALS['unitname'] ."')";
//echo $query;
try{
//echo $query ."\n";
runsql($query);
//echo "made it\n";
}catch(Exception $e) {
	//$STDERR = fopen('/var/log/templogerror.log', 'wb');
	//fwrite($STDERR,'Caught exception: ',  $e->getMessage(), "\n");
	echo "Caught exception: ",  $e->getMessage(), "\n";
	//fclose($STDERR);
	continue;
}
}
//'*******************************************************************************


//'*******************************************************************************
function getip()
{
$hostsipaddress = str_replace("\n","",shell_exec("ifconfig wlan0 | grep 'inet addr' | awk -F':' {'print $2'} | awk -F' ' {'print $1'} 2>&1"));

if ($hostsipaddress != "") {
	//echo "\n" .$hostsipaddress ."\n";
	return $hostsipaddress;
}else{
	//echo "N/A\n";
	return "N/A";
}
}
//'*******************************************************************************

//'*******************************************************************************
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
//'*******************************************************************************


//'*********************************************************************************************
function QueryIntoArray($query){
        settype($retval,"array");
$username = $GLOBALS['ini_array']['database']['user_name'];
$password = $GLOBALS['ini_array']['database']['passwd'];
$database = $GLOBALS['ini_array']['database']['db'];
$host = $GLOBALS['ini_array']['database']['dbhost'];

$connection = mysql_connect($host,$username,$password);
continue;

@mysql_select_db($database); //or die( "Unable to select database");

$result= mysql_query($query);
        if(!$result){
//print "Query Failed";
	die;
        }        
        for($i=0;$i<mysql_numrows($result);$i++){
                for($j=0;$j<mysql_num_fields($result);$j++){
			if(is_null(mysql_result($result,$i)))
			{
			$retval[$i][mysql_field_name($result,$j)] = ""; //mysql_result($result,$i,mysql_field_name($result,$j));			
			}else{                        
			$retval[$i][mysql_field_name($result,$j)] = mysql_result($result,$i,mysql_field_name($result,$j));
			}
                }//end inner loop
        }//end outer loop
mysql_close($connection);
return $retval;
}//end function
//'*********************************************************************************************




//'*********************************************************************************************
function runsql($query){
settype($retval,"array");
$username = $GLOBALS['ini_array']['database']['user_name'];
$password = $GLOBALS['ini_array']['database']['passwd'];
$database = $GLOBALS['ini_array']['database']['db'];
$host = $GLOBALS['ini_array']['database']['dbhost'];
try{
$connection = mysql_connect($host,$username,$password);
}catch(Exception $e) {

	$STDERR = fopen('/var/log/templogerror.log', 'wb');
	fwrite($STDERR,'Caught exception: ',  $e->getMessage(), "\n");
	fclose($STDERR);
	return;
}
@mysql_select_db($database); //or die( "Unable to select database");
mysql_query($query);// or die( "Query failed in RunSql " .mysql_error());
mysql_close($connection);
}//end function
//'*********************************************************************************************


//'*******************************************************************************
function showusage()
{
	echo "temp1.php Rev ". $GLOBALS['revmajor'] ."." .$GLOBALS['revminor'] ."\n";
	echo "Usage: temp1.php [option]...\n Using the beaglebone black as a temp logger\n";
	echo "\t this is usualy run from tempd in daemon mode\n";
	echo "Mandatory arguments\n";
	echo "  -h, \t This help\n";
	echo "  -r, \t Used for debuging from console\n";
	echo "  -D, \t Daemon mode usualy called from temp1d\n";
	echo "\n\n";
}
//'*******************************************************************************


?>
