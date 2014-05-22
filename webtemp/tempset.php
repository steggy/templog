
<?php
//This is for the company bio-rad

//Change value below to your company name
$companyname = 'bio-rad';

//Change value below to the logo file located in the images directory
$CompanyLogo = "logosm.gif";

//MySQL info for database connection
$dbusername="templog";
$dbpassword="templog123";
$database="templogger";
$dbhost = "10.3.101.219";

//Temp sensor name
$sname = '28-00000574cbe8';

//Sample rate in seconds
$samprate = 300;

//unit name 
$unitname = "bbb-ubuntu12-02";


//Just the domain name of the company NO @ signs
$EmailDomain = "COMPANY.com";

//Change to the persons email who administrating the system
//This can be more than one email address seperated by semicolan
$AdminEmail = "Admin@yourcompany.com";

//Change to the address used by the request system
$ReqEmail = "request@yourcompany.com";

//The length in days the default due date will be
$DueDateLen = "7";


//Homedir = request.ServerVariables("APPL_PHYSICAL_PATH")

?>
