<?php
/*
*	Initializers and requires
*/

date_default_timezone_set('US/Eastern');
error_reporting(0);
require_once("dataAccess.php");
$currentDate = date('Y-m-d h:i:s', time());

/*
*	Attempt to link incoming data to active session
*/

$motion  = $_GET['m'];
$light = $_GET['l'];
$deviceId = $_GET['d'];
$prevStudySession = getMostRecentStudySession();

if(!isset($prevStudySession['stopTime']) || empty($prevStudySession['stopTime']) || $prevStudySession['stopTime'] == NULL){

	$studySessionId = $prevStudySession['studySessionsId'];

}

/*
*	Sensor Recognition
*/

if($light){
	echo '<br>light detected!<br>';
}

if($motion){
	echo '<br>motion detected!<br>';
}

insertPiFeed($studySessionId, $light, $motion, $deviceId);//sends sensor input to database

/*
*	Decision making:
*	First grab data for previous 10 sensor data entries
*/

$feed = selectPiFeed(10);
$prevEntries = array();
$prevTimes = array();

while($row = mysqli_fetch_assoc($feed)){

	array_push($prevEntries, $row);

}

/*
*	Conditions to start and end a study session
*/

if($light && !$prevEntries[1]['light']){

	echo '<br>start new study session<br>';
	insertNewStudySession($deviceId);

}else if(!$light && $prevEntries[1]['light']){
	
	if(!isset($prevStudySession['stopTime']) || empty($prevStudySession['stopTime']) || $prevStudySession['stopTime'] == NULL){

		echo '<br>end previous study session<br>';
		setStudySessionEnd($prevStudySession['studySessionsId'], $prevStudySession['roomsId']);

	}

}

/*
*	Conditions to count a break
*/

if($motion){

	if(!$prevEntries[1]['motion']){

		$lastTime = $prevEntries[2]['timestamp'];
		$timeSinceLast = findTimeDifference($currentDate, $lastTime);

		if($timeSinceLast['minutes'] >= 2 && $timeSinceLast['minutes'] <= 30){

			addBreakToSession($prevStudySession['studySessionsId']);
			echo '<br>It\'s been '.$timeSinceLast['minutes'].' minutes since the last movement, there has been a break<br>';

		}else if($timeSinceLast['minutes'] > 30){

			echo '<br>It\'s been a while! Since we\'ve had movement<br>';

			//after 30 minutes of no detected movement, better to just start a fresh session

			if(!isset($prevStudySession['stopTime']) || empty($prevStudySession['stopTime']) || $prevStudySession['stopTime'] == NULL){

				echo '<br>end previous study session<br>';
				setStudySessionEnd($prevStudySession['studySessionsId'], $prevStudySession['roomsId']);

			}

			echo '<br>start new study session<br>';
			insertNewStudySession($deviceId);

		}

	}

}

/*
* Time math, for decision making
*/
$currentDate = date('Y-m-d h:i:s', time());
$lastTime = $prevEntries[1]['timestamp'];

$timeSinceLast = findTimeDifference($currentDate, $lastTime);

/*
*	User defined functions
*/

function timeDate($timestamp){

	$dateTime = explode(' ', $timestamp);
	$date = explode('-', $dateTime[0]);
	$y = $date[0];
	$m = $date[1];
	$d = $date[2];
	$time = explode(':', $dateTime[1]);
	$h = $time[0];
	$i = $time[1];
	$s = $time[2];

	$timestamp = mktime($h,$i,$s,$m,$d,$y);

	return $timestamp;

}

?>