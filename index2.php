<!DOCTYPE html>

<html>

	<head>
		<title>Student Registration</title>
		
		<link rel="stylesheet" href="./styles.css">
		
	</head>
	
	<body>
	
		<h1>Registered Students</h1>
	
		<button onclick="window.location.href='index.php'" class="button-design">Registration Form</button>
		<button onclick="window.location.href='index2.php'" class="button-design">Check Students Registered</button>
		<br><br>	
		
		<?php 
			
			$servername = "your-servername-here";
			$username = "your-username-here";
			$password = "your-password-here";
			$dbname = "StudentRegistrationDB";
			
			// Create Connection
			$conn = new mysqli($servername, $username, $password, $dbname);
			
			//Check Connection
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}
			
			$sql = "SELECT id, firstName, lastName, projectTitle, emailAddress, phoneNumber, timeSlot FROM Students";
			
			$result = $conn->query($sql);
			
			if ($result->num_rows > 0) {
				echo "<table><tr>
				<th>ID</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Project Title</th>
				<th>Email Address</th>
				<th>Phone Number</th>
				<th>Time Slot</th>
				</tr>";
				
				// output data of each row
				while ($row = $result->fetch_assoc()) {
					echo "<tr><td>" . $row["id"] . "</td><td>" . $row["firstName"] . "</td><td>" . $row["lastName"] . "</td><td>" . $row["projectTitle"] . "</td><td>" . $row["emailAddress"] . "</td><td>" . $row["phoneNumber"]  . "</td><td>" . $row["timeSlot"] . "</td></tr>";
				}
				echo "</table>";
			}
			else {
				echo "0 Results";
			}
			
			$conn->close();
			
		?>		
		
		
	
	</body>

</html>
