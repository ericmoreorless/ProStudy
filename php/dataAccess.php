<?php
date_default_timezone_set('US/Eastern');

function findTimeDifference($timea, $timeb){

	$seconds = strtotime($timea) - strtotime($timeb);

	$days    = floor($seconds / 86400);
	$hours   = floor(($seconds - ($days * 86400)) / 3600);
	$minutes = floor(($seconds - ($days * 86400) - ($hours * 3600))/60);
	$seconds = floor(($seconds - ($days * 86400) - ($hours * 3600) - ($minutes*60)));

	$difference = array(
		"days" => $days,
		"hours" => $hours,
		"minutes" => $minutes,
		"seconds" => $seconds
		);

	return $difference;

}

function makeConnection(){
	$hostname = "ProStudyDB.db.10390291.hostedresource.com";
	$username = "ProStudyDB";
	$dbname = "ProStudyDB";
	$password = "Art1ch0k3!";

	//Connecting to your database
	if ($mysqli = mysqli_connect($hostname, $username, $password, $dbname)) {

	    //echo '<br>000 connection successful!<br>';

	}else{

		echo '<br>000 Failed to connect to MySQL!<br>';
		die();

	}

	return $mysqli;
}

function insertPiFeed($studySessionId, $light, $motion, $deviceId){

	$currentTime = date('Y-m-d h:i:s', time());

	$con = makeConnection();

	if(isset($studySessionId) && !empty($studySessionId)){

		$sql = 'INSERT INTO `ProStudyDB`.`piFeed` (
			`piFeedId` ,
			`studySessionsId` ,
			`devicesId`,
			`light` ,
			`motion`,
			`timestamp`
			)
			VALUES (
			NULL, 
			'.$studySessionId.' , 
			'.$deviceId.',
			'.$light.', 
			'.$motion.',
			\''.$currentTime.'\'
			);
		';

	}else{

		$sql = 'INSERT INTO `ProStudyDB`.`piFeed` (
			`piFeedId` ,
			`studySessionsId` ,
			`devicesId`,
			`light` ,
			`motion`,
			`timestamp`
			)
			VALUES (
			NULL, 
			NULL, 
			'.$deviceId.',
			'.$light.', 
			'.$motion.',
			\''.$currentTime.'\'
			);
		';

	}

	
	if($res = mysqli_query($con, $sql)){

		//echo '<br>001 query successful!<br>';

	}else{

		echo '<br>001 query failed!<br>'.$sql;

	}

	return $res;	

}

function insertNewStudySession($deviceId){

	$con = makeConnection();
	$currentTime = date('Y-m-d h:i:s', time());

	//Get room id

	$sql = ' SELECT * FROM `devices` WHERE `devicesId` = '.$deviceId;

	if($res = mysqli_query($con, $sql)){

		$row = mysqli_fetch_assoc($res);
		$roomId = $row['roomsId'];
		//echo '<br>002a query successful!<br>';

	}else{

		echo '<br>002a query failed<br>';

	}

	//get piFeed id

	$sql = ' SELECT * FROM `piFeed` ORDER BY `piFeedId` DESC LIMIT 0, 1';

	if($res = mysqli_query($con, $sql)){

		$row = mysqli_fetch_assoc($res);
		$piFeedId = $row['piFeedId'];
		//echo '<br>002b query successful!<br>';

	}else{

		echo '<br>002b query failed<br>';

	}

	//add study session

	$sql = 'INSERT INTO `ProStudyDB`.`studySessions` (
			`studySessionsId` ,
			`studentsId` ,
			`roomsId` ,
			`devicesId` , 
			`startTime` ,
			`stopTime` ,
			`breakNumber` ,
			`timeSpentWriting` ,
			`timeSpentReading` ,
			`class`
			)
			VALUES (
			NULL ,
			NULL ,
			'.$roomId.' ,
			'.$deviceId.' ,  
			\''.$currentTime.'\' , 
			NULL , 
			NULL , 
			NULL , 
			NULL , 
			NULL
			);
	';
	
	if($res = mysqli_query($con, $sql)){

		//echo '<br>002c query successful!<br>';

	}else{

		echo '<br>002c query failed!<br>'.$sql;

	}

	//get newly created study session's id

	$sql = ' SELECT * FROM `studySessions` ORDER BY `studySessionsId` DESC LIMIT 0, 1';


	if($res = mysqli_query($con, $sql)){

		$row = mysqli_fetch_assoc($res);
		$studySessionId = $row['studySessionsId'];
		//echo '<br>002d query successful!<br>';

	}else{

		echo '<br>002d query failed<br>';

	}

	//store this id in the most recent piFeed entry

	$sql = 'UPDATE `ProStudyDB`.`piFeed` SET `studySessionsId` = '.$studySessionId.' WHERE `piFeed`.`piFeedId` = '.$piFeedId.';';


	if($res = mysqli_query($con, $sql)){

		$row = mysqli_fetch_assoc($res);
		//echo '<br>002e query successful!<br>';

	}else{

		echo '<br>002e query failed<br>';

	}

	//Set room to unavailable

	$sql = 'UPDATE `ProStudyDB`.`rooms` SET `available` = 0 WHERE `rooms`.`roomsId` = '.$roomId.';';


	if($res = mysqli_query($con, $sql)){

		//echo '<br>002f query successful!<br>';

	}else{

		echo '<br>002f query failed<br>'.$sql;

	}

	return $res;

}

function setStudySessionEnd($studySessionId, $roomId){

	$currentTime = date('Y-m-d h:i:s', time());

	$con = makeConnection();

	//Set room to available

	$sql = 'UPDATE `ProStudyDB`.`rooms` SET `available` = 1 WHERE `rooms`.`roomsId` = '.$roomId.';';


	if($res = mysqli_query($con, $sql)){

		//echo '<br>003a query successful!<br>';

	}else{

		echo '<br>003a query failed<br>'.$sql;

	}

	// end study session

	$sql = 'UPDATE `ProStudyDB`.`studySessions` SET `stopTime` = \''.$currentTime.'\' WHERE `studySessions`.`studySessionsId` = '.$studySessionId.';';

	if($res = mysqli_query($con, $sql)){

		//echo '<br>003b query successful!<br>';

	}else{

		echo '<br>003b query failed<br>';

	}

	return $res;

}

function getMostRecentStudySession(){

	$con = makeConnection();

	$sql = ' SELECT * FROM `studySessions` ORDER BY `studySessionsId` DESC LIMIT 0, 10';

	if($res = mysqli_query($con, $sql)){

		$row = mysqli_fetch_assoc($res);

		//echo '<br>004 query successful!<br>';

	}else{

		echo '<br>004 query failed<br>';

	}

	return $row;

}

function selectPiFeed($limit){

	$con = makeConnection();

	$sql = ' SELECT * FROM `piFeed` ORDER BY `timestamp` DESC LIMIT 0, '.$limit;

	if($res = mysqli_query($con, $sql)){

		//echo '<br>005 query successful!<br>';

	}else{

		echo '<br>005 query failed<br>';

	}

	return $res;

}

function addBreakToSession($studySessionId){

	$currentTime = date('Y-m-d h:i:s', time());

	$con = makeConnection();

	$sql = 'SELECT * FROM `studySessions` WHERE `studySessionsId` = '.$studySessionId;

	if($res = mysqli_query($con, $sql)){

		$row = mysqli_fetch_assoc($res);
		$newNumberOfBreaks = $row['breakNumber'] + 1;
		//echo '<br>006a query successful!<br>';

	}else{

		echo '<br>006a query failed<br>';

	}

	$sql = 'UPDATE `ProStudyDB`.`studySessions` SET `breakNumber` = \''.$newNumberOfBreaks.'\' WHERE `studySessions`.`studySessionsId` = '.$studySessionId.';';

	if($res = mysqli_query($con, $sql)){

		//echo '<br>006b query successful!<br>';

	}else{

		echo '<br>006b query failed<br>';

	}

	return $res;

}

function insertNewStudent($studentFirstName, $studentLastName){

	$currentTime = date('Y-m-d h:i:s', time());
	$con = makeConnection();

	$sql = 'INSERT INTO `ProStudyDB`.`students` (
			`studentsId` ,
			`firstName` ,
			`lastName`
			)
			VALUES (
			NULL ,
			\''.$studentFirstName.'\' ,
			\''.$studentLastName.'\'
			);
	';

	if($res = mysqli_query($con, $sql)){

		//echo '<br>007 query successful!<br>';

	}else{

		echo '<br>007 query failed<br>'.$sql;

	}

	return $res;

}

function linkStudentDataToStudySession($studySessionId, $studentId, $class){

	$currentTime = date('Y-m-d h:i:s', time());
	$con = makeConnection();

	$sql = 'UPDATE `ProStudyDB`.`studySessions` SET `studentsId` = \''.$studentId.'\', `class` = \''.$class.'\' WHERE `studySessions`.`studySessionsId` = '.$studySessionId.';';

	if($res = mysqli_query($con, $sql)){

		//echo '<br>008 query successful!<br>';

	}else{

		echo '<br>008 query failed<br>';

	}

	return $res;

}

function confirmNewStudent($studentFirstName, $studentLastName){

	$currentTime = date('Y-m-d h:i:s', time());
	$con = makeConnection();

	$sql = 'SELECT * FROM `students` WHERE `firstName` = \''.$studentFirstName.'\' AND `lastName` = \''.$studentLastName.'\'';

	if($res = mysqli_query($con, $sql)){

		$row = mysqli_fetch_assoc($res);
		$count = mysqli_num_rows($res);

		//echo '<br>009 query successful!<br>';

	}else{

		$count = 0;
		echo '<br>009 query failed<br>';

	}

	if($count > 0){

		return $row['studentsId'];

	}else{

		return 0;

	}

	
}

function checkRooms(){

	$roomsData = array();

	$currentTime = date('Y-m-d h:i:s', time());
	$con = makeConnection();

	$sql = 'SELECT * FROM `rooms` LIMIT 0,10';

	if($res = mysqli_query($con, $sql)){

		while($row = mysqli_fetch_assoc($res)){

			array_push($roomsData, $row);

		}

		//echo '<br>010 query successful!<br>';

	}else{

		echo '<br>010 query failed<br>';

	}

	
	return $roomsData;

	
}

function selectPiFeedBySession($studySessionId, $limit){

	$con = makeConnection();

	if($limit == 0){

		$sql = ' SELECT * FROM `piFeed` WHERE `studySessionsId` = '.$studySessionId.' ORDER BY `piFeedId` DESC';


	}else{

		$sql = ' SELECT * FROM `piFeed` WHERE `studySessionsId` = '.$studySessionId.' ORDER BY `piFeedId` DESC LIMIT 0, '.$limit;


	}

	if($res = mysqli_query($con, $sql)){

		//echo '<br>011 query successful!<br>';

	}else{

		echo '<br>011 query failed<br>';

	}

	return $res;

}

?>