<?php

//
//    Adam Boyd at Windstream Communications assembled the base script found in doquery.php
//    Jonathan Wagner modified it to allow for Batch Adds
//            


// set appropriate timezone for your server
date_default_timezone_set('America/New_York');
include("usepass.php");  //ldap authentication.
// require_once('../loggen.class.php'); //loggen is a class built to send logs from php scripts to Splunk via syslog
// commenting out all loggen comments contact me if you want to see that code
//$log = new logGen('e0145831','./xmatters_createuser.log', TRUE);
//$log->logThis(LOG_INFO,"xMatters Create User log");

// send email to new user
function sendUserEmail($email, $fname, $lname, $username){
$to = $email;
$subject = "New xMatters Account Information";
$txt = "Hey ".$fname." ".$lname.",\nThis is in regards to your request for a new xMatters account. ";
$txt.= "\n When you get a chance, check to see if you can log in to xMatters now using your new CSO credentials ( ".$username." )";
$txt.= "\n at https://windstream.hosted.xmatters.com/ \n";
$txt.= "\nHere's some information on xMatters, there's a document at the bottom for setting up subscriptions as well:";
$txt.= "\nhttps://wiki.windstream.com/display/NOSS/NMS+-+FAQ%27s";
$txt.= "\nhttps://wiki.windstream.com/display/NOSS/xMatters+Information";
$txt.= "\nhttps://wiki.windstream.com/download/attachments/244482085/xMatters%20Subscriptions.doc?api=v2";
$txt.="\n\nReach out to me if you need any help. \nSincerely, \nJonathan Wagner\n\r";
$headers = "From: Jonathan.Wagner@windstream.com" . "\r\n" ."CC: jonathan.wagner@windstream.com";


$GLOBALS['log']->logThis(LOG_INFO,$to);
$GLOBALS['log']->logThis(LOG_INFO,$subject);
$GLOBALS['log']->logThis(LOG_INFO,$headers);
$GLOBALS['log']->logThis(LOG_INFO,$txt);

$retval = mail($to,$subject,$txt,$headers);

return($retval);

}

// Query LDAP
function adQueryUser($enumber) {
  $uid = "ldapdnuser";
  $pass = "ldap password for above";

  // Connect to the AD server
  $host = "ad.yourco.com";
  $dn = "ou=yourco,dc=yourco,dc=com";
  $user = "YOURCO\\".$uid;

  $ds = @ldap_connect($host);
  if ($ds) { $ldapbind = @ldap_bind($ds, $user, $pass); }

  $filter = "(samaccountname=$enumber)";
  $attrs = array("mail");
  $r = @ldap_search($ds, $dn, $filter, $attrs);
  $l_result = @ldap_get_entries($ds, $r);

  if($l_result["count"] == 0) { exit; }
  else {
    array_shift($l_result);
    foreach($l_result as $prsn) {
      return $prsn["mail"][0];
//      print_r($l_result);
    }
  }

  @ldap_close($host);
  return;
}

// Get User
function xmQueryUser($enumber) {
  $user = "xMatters_user_w_permissions";
  $pass = "passwordToAbove";
  $url = "https://YOURCO.hosted.xmatters.com/api/xm/1/people/" . $enumber . "?embed=roles";

  $curl = curl_init();

  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8'));
  curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_SSLVERSION, 5);
  curl_setopt($curl, CURLOPT_POST, 0);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
  $output = curl_exec($curl);

  $GLOBALS['log']->logThis(LOG_INFO,$curl);
  // print curl_error($curl);
  $GLOBALS['log']->logThis(LOG_ERR, curl_error($curl));
  //var_dump($output);
  $GLOBALS['log']->logThis(LOG_INFO, $output);

  return $output;
}

// Create User
function xmCreateUser($enumber, $first, $last, $tz) {
  $user = "xMatters_user_w_permissions";
  $pass = "passwordToAbove";
  // non-prod
  $url = "https://YOURCOP-np.hosted.xmatters.com/api/xm/1/people";

  $curl = curl_init();

  $data = array("targetName" => $enumber,
                "firstName" => $first,
                "lastName" => $last,
                "recipientType" => "PERSON",
                "language" => "en",
                "timezone" => $tz,
                "webLogin" => $enumber,
                "site" => "Site-Key-obtained-from-xmatters", // example  "19918641-3419-a042-0523-2b653fbd3679",
                "roles" => array("Standard User"),
                "status" => "ACTIVE",
                "supervisors" => array("array field from xMatters"));


  $data = json_encode($data);

  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_SSLVERSION, 5);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
  $output = curl_exec($curl);

//  print $output;
  $GLOBALS['log']->logThis(LOG_INFO, $output);
  return $output;
}

// Create Device
function xmCreateDevice($id, $email) {
  $user = "xMatters_user_w_permissions";
  $pass = "passwordToAbove";
  $url = "https://YOURCO.hosted.xmatters.com/api/xm/1/devices";

  $curl = curl_init();

  $data = array("recipientType" => "DEVICE",
                "defaultDevice" => true,
                "deviceType" => "EMAIL",
                "owner" => $id,
                "emailAddress" => $email,
                "name" => "Work Email",
                "testStatus" => "TESTED");

  $data = json_encode($data);

  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_SSLVERSION, 5);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
  $output = curl_exec($curl);

//  print $output;

  return $output;
}

// Get People
function xmQueryUsers($offset) {
  $users = array();

  $user = "xMatters_user_w_permissions";
  $pass = "passwordToAbove";

  $curl = curl_init();

  $url = "https://YOURCO.hosted.xmatters.com/api/xm/1/people?offset=" . $offset . "&limit=1000";

  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_SSLVERSION, 5);
  curl_setopt($curl, CURLOPT_POST, 0);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
  $output = curl_exec($curl);
  $log->logThis(LOG_INFO, $output);
  $output_arr = json_decode($output);

  foreach($output_arr->data as $user) {
    $users[] = $user->targetName;
  }
  // $log->logThis(LOG_INFO, $curl);
  curl_close($curl);

//  print_r($users);

  return $users;
}

/*
 * MAIN
 */

if(isset($_GET['enumber'])) {
  $enumber = $_GET['enumber'];
}
else {
  print "<br>You must supply an eNumber.<br>";
  // $log->logThis(LOG_ERR, "No eNumber Provided.");
  return;
}

if(isset($_GET['first'])) {
  $first = $_GET['first'];
}
else {
  // $log->logThis(LOG_ERR, "No First Name Provided.");
  print "<br>You must supply a First Name.<br>";
  return;
}

if(isset($_GET['last'])) {
  $last = $_GET['last'];
}
else {
  // $log->logThis(LOG_ERR, "No Last Name Provided.");
  print "<br>You must supply a Last Name.<br>";
  return;
}

$tz = "";
if(isset($_GET['tz'])) {
  $tz = $_GET['tz'];
}

$timezone = "US/Eastern";

// Convert Timezone
if($tz == "Eastern") {
  $timezone = "US/Eastern";
}
elseif($tz == "Central") {
  $timezone = "US/Central";
}
elseif($tz == "Mountain") {
  $timezone = "US/Mountain";
}
elseif($tz == "Pacific") {
  $timezone = "US/Pacific";
}
$log->logThis(LOG_INFO, "Timezone selected: $timezone");
// Check to see if user already exists
$xmq_return = json_decode(xmQueryUser($enumber));
//print_r($xmq_return);
// $log->logThis(LOG_INFO, $xmq_return);

if(isset($xmq_return->id)) {
  print "<br><br><strong>Existing user was found: " . $xmq_return->id . "</strong><br>";
  // $log->logThis(LOG_ERR, "Existing user was found: $xmq_return->id");
  return;
}
else {
  print "<br>User does not exist in xMatters yet.<br>";
  // $log->logThis(LOG_ERR, "User does not exist in xMatters yet.");
}

// Use inputted variables to create the user
$xm_return = json_decode(xmCreateUser($enumber, $first, $last, $timezone));
//print_r($xm_return);
// $log->logThis(LOG_INFO, $xm_return);
if(isset($xm_return->id)) {
  print "<br><strong>Created user:</strong> " . $xm_return->id . "<br>";
  // $log->logThis(LOG_INFO, "Created user: " . $xm_return->id );
  $notify=sendUserEmail($ad_return, $first, $last, $enumber);
  // $log->logThis(LOG_INFO, "Email Sent to user: " . $notify );
}
else {
  print "<br><strong>Did not create user. User Already Exists. Exiting.</strong><br>";
  //print_r($xm_return);
  // $log->logThis(LOG_ERR, "Did not create user. Already exists.");
  // $log->logThis(LOG_ERR, $xm_return);
}

// Query LDAP for Email Address
$ad_return = adQueryUser($enumber);
// $log->logThis(LOG_INFO, $ad_return);

if(strlen($ad_return) == 0) {
  print "<br>No valid email address found in Active Directory. Please manually add.<br>";
  // $log->logThis(LOG_ERR, "No valid email address found in Active Directory. Please manually add.");
  return;
}
else {
  print "<br><strong>Email address found: </strong>" . $ad_return . "<br>";
  // $log->logThis(LOG_INFO, "Email address found: " . $ad_return);
}

// Create the user's device
$dv_return = json_decode(xmCreateDevice($xm_return->id, $ad_return));

if(isset($dv_return->id)) {
  print "<br><strong>Created device connected to user:</strong> " . $xm_return->id . "<br>";
  // $log->logThis(LOG_INFO, "Created device connected to user: " . $xm_return->id );
}
else {
  print "<br><strong>Unable to create device for user. Please open ticket for manual add.</strong><br>";
  // $log->logThis(LOG_ERR,"Unable to create device for user. Please manually add.");
}

/*
 * END
 */

?>
