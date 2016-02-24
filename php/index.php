<html>
<head>
	<title>ProStudy Database Interface</title>
	<style type="text/css">
	.hasRightBorder{
		margin-right: 20px;
		margin-left: 20px;
		border-right: solid #000;
	}
	table{
		
		border-collapse: collapse;
	}
	</style>
</head>
<body>

	<b>Welcome to ProStudy!</b>
	<br>
	<br>
	<?php
	require_once('dataAccess.php');
	$roomsData = checkRooms();
	echo '
	<table cellspacing=0>
		<thead>
			<tr>
				<th class="hasRightBorder">Room Number</th>
				<th>Available</th>
			</tr>
		</thead>
		<tbody>
	';
	foreach ($roomsData as $room) {
		if($room['roomNumber']){

			$available = 'NO';

		}else{

			$available = 'YES';

		}
		echo '
			<tr>
				<td class="hasRightBorder">'.$room['roomNumber'].'</td>
				<td>'.$available.'</td>
			</tr>';
	}
	echo '
		</tbody>
	<table>
	';
	?>
	<br>
	<br>
	<form action="sessionData.php" method="POST">
		First name:<br>
		<input type="text" id="firstname" name="firstname" placeholder="John"><br>
		Last name:<br>
		<input type="text" id="lastname" name="lastname" placeholder="Doe"><br>
		What class are you studying for:<br>
		<input type="text" id="class" name="class" placeholder="ex: ED 1 or EGN 4950C"><br><br>

		<input type="submit" value="Submit">
	</form>

</body>
</html>