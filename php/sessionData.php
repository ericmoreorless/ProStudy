<html>
<head>
	<title>ProStudy Study Session Data</title>
</head>
<body>
<?php
error_reporting(E_ALL);
require_once("dataAccess.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

	$studentFirstName = $_POST['firstname'];
	$studentLastName = $_POST['lastname'];
	$class = $_POST['class'];
	$prevStudySession = getMostRecentStudySession();

	//first check if this is a new student or an existing one
	$studentId = confirmNewStudent($studentFirstName, $studentLastName);
	if($studentId == 0){

		insertNewStudent($studentFirstName, $studentLastName);
		$studentId = confirmNewStudent($studentFirstName, $studentLastName);

	}	

	//if there is an open session, link this student to that session and redirect so they can view their data
	if(!isset($prevStudySession['stopTime']) || empty($prevStudySession['stopTime']) || $prevStudySession['stopTime'] == NULL){

		linkStudentDataToStudySession($prevStudySession['studySessionsId'], $studentId, $class);

		$currentTime = date('Y-m-d h:i:s', time());
		$sessionStartTime = $prevStudySession['startTime'];
		$timeSpentMoving = 0;
		$totalTimeStudied = 0;

		$thisSessionFeed = selectPiFeedBySession($prevStudySession['studySessionsId'], 0);
		$feedData = array();
		while($data = mysqli_fetch_assoc($thisSessionFeed)){

			array_push($feedData, $data);

		}

		for ($i=0; $i < $thisSessionFeed->num_rows; $i++) { 
			
			$motion = $feedData[$i]['motion'];

			if($motion == 0 && $i > 0){

				$i--;
				$lastMotion = $feedData[$i]['motion'];

				if($lastMotion == 1){

					$timeMovementStart = strtotime($feedData[$i]['timestamp']);
					$i++;
					$timeMovementEnd = strtotime($feedData[$i]['timestamp']);
					$movementDuration = date('i:s', $timeMovementStart - $timeMovementEnd);
					$q = explode(':', $movementDuration);
					$timeSpentMoving += ($q[0] * 60) + $q[1];

				}else{

					$i++;

				}
				

			}


		}

		if($prevStudySession['breakNumber'] > 0){

			$numberofBreaks = $prevStudySession['breakNumber'] ;

		}else{

			$numberofBreaks = 0;

		}

		$ct = explode(' ', $currentTime);
		$sst = explode(' ', $sessionStartTime);

		$duration = date('i:s', strtotime($currentTime) - strtotime($sessionStartTime));
		$t = explode(':', $duration);
		$secondsSpentStudying = ($t[0] * 60) + $t[1];
		$percentTimeSpentMoving = round($timeSpentMoving / $secondsSpentStudying * 100, 1);

		$hours   = floor($duration / 3600);
		$minutes = floor($duration - ($hours * 3600)/60);
		$seconds = floor($duration - ($hours * 3600) - ($minutes*60));

		echo '
			<p>
				You have been studying for: 
				<br><b>'.$t[0].'</b> minutes <b>'.$t[1].'</b> seconds
				<br>You have taken <b>'.$numberofBreaks.'</b> break(s)
				<br>You moved around <b>'.$percentTimeSpentMoving.'%</b> of the time
			</p>';

		if($percentTimeSpentMoving < 10){

			echo '
				<p>You should move around more!
				<br>Getting the blood flowing is good for your brain!
				</p>';

		}else if($percentTimeSpentMoving > 30){

			echo '
				<p>You should get back to business. 
				<br>It seems like you\'re getting distracted!
				</p>';

		}else{

			echo '
				<p>You are moving a healthy amount. 
				<br>Keep up the good work!
				</p>';

		}

	}else{

		die('site is kill');

	}

}else{

	die('site is kill');

}
?>
</body>
</html>