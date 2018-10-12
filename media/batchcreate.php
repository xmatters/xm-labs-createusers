<?php

//
// Adam Boyd at Windstream Communications assembled the base script found in doquery.php
// Jonathan Wagner modified it to allow for Batch Adds
//

//
//This opens the csv file newxmattersusers.csv This file contains a csv list
// username, LastName, FirstName, Timezone,
// one user per line
// all users must be able to be authorized against LDAP/AD

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
$txt.="\n\nReach out to me if you need any help. \nSincerely, \nYour Name\n\r";
$headers = "From: Your.Name@YOURCO.com" . "\r\n" ."CC: your.name@YOURCO.com";

mail($to,$subject,$txt,$headers);

}


// Query LDAP
function adQueryUser($enumber) {
  $uid = "adQuesryUser";
  $pass = "thatPassword";

  // Connect to the Windstream AD server
  $host = "ad.YOURCO.com";
  $dn = "ou=YOURCO,dc=YOURCO,dc=com";
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
    }
  }

  @ldap_close($host);
  return;
}

// Get User
function xmQueryUser($enumber) {
  $user = "xMattersAccountWithPrivs";
  $pass = "PAsswordForAbove";
//  $url = "https://YOURCO-np.hosted.xmatters.com/api/xm/1/people/" . $enumber . "?embed=roles";
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

  print curl_error($curl);

  return $output;
}

// Create User
function xmCreateUser($enumber, $last, $first, $tz) {
  $user = "xMattersAccountWithPrivs";
  $pass = "PAsswordForAbove";
//  $url = "https://YOURCO-np.hosted.xmatters.com/api/xm/1/people";
  $url = "https://YOURCO.hosted.xmatters.com/api/xm/1/people";

  $curl = curl_init();

  $data = array("targetName" => $enumber,
                "firstName" => $first,
                "lastName" => $last,
                "recipientType" => "PERSON",
                "language" => "en",
                "timezone" => $tz,
                "webLogin" => $enumber,
                "site" => "Site-Number-form-xMAtters",
                "roles" => array("Standard User"),
                "status" => "ACTIVE",
                "supervisors" => array("supervisor-user-array-key-from-xmatters"));


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


  return $output;
}

// Create Device
function xmCreateDevice($id, $email) {
  $user = "xMattersAccountWithPrivs";
  $pass = "PAsswordForAbove";
//  $url = "https://YOURCO-np.hosted.xmatters.com/api/xm/1/devices";
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


  return $output;
}

// Get People
function xmQueryUsers($offset) {
  $users = array();

  $user = "xMattersAccountWithPrivs";
  $pass = "PAsswordForAbove";

  $curl = curl_init();

//  $url = "https://YOURCO-np.hosted.xmatters.com/api/xm/1/people?offset=" . $offset . "&limit=1000";
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

  $output_arr = json_decode($output);

  foreach($output_arr->data as $user) {
    $users[] = $user->targetName;
  }

  curl_close($curl);

  return $users;
}

/*
 * MAIN
 */

$file = file_get_contents('./newxmattersusers.csv');
$file_array=explode("\n",$file);

foreach($file_array as $newuser)
{
  $thisuser=explode(",",$newuser);
  $enumber = $thisuser[0];
  $first = $thisuser[1];
  $last = $thisuser[2];
  $tz = $thisuser[3];


$timezone = "US/Eastern";

// Convert Timezone
if(($tz == "Eastern") || ($tz == "ET")) {
  $timezone = "US/Eastern";
}
elseif(($tz == "Central") || ($tz == "CT")) {
  $timezone = "US/Central";
}
elseif(($tz == "Mountain") || ($tz == "MT")) {
  $timezone = "US/Mountain";
}
elseif(($tz == "Pacific") || ($tz == "PT")) {
  $timezone = "US/Pacific";
}

// Check to see if user already exists
$xmq_return = json_decode(xmQueryUser($enumber));
print_r($xmq_return);

if(isset($xmq_return->id)) {
  print "Existing user was found: " . $xmq_return->id . "<br>";
  next;
}
else {
  print "User does not exist in xMatters yet.<br>";
}

// Use inputted variables to create the user
$xm_return = json_decode(xmCreateUser($enumber, $first, $last, $timezone));
print_r($xm_return);
if(isset($xm_return->id)) {
  print "Created user: " . $xm_return->id . "<br>";
}
else {
  print "Did not create user. Exiting.<br>";
  print_r($xm_return);
}

// Query LDAP for Email Address
$ad_return = adQueryUser($enumber);

if(strlen($ad_return) == 0) {
  print "No valid email address found in Active Directory. Please manually add.<br>";
  next;
}
else {
  print "Email address found: " . $ad_return . "<br>";
}

// Create the user's device
$dv_return = json_decode(xmCreateDevice($xm_return->id, $ad_return));

if(isset($dv_return->id)) {
  print "Created device connected to user: " . $xm_return->id . "<br>";
  sendUserEmail($ad_return, $first, $last, $enumber);
}
else {
  print "Unable to create device for user. Please manually add.<br>";
}
} 
/*
 * END
 */

?>
