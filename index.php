<!DOCTYPE html>

<html>
	<head>
		<title>Student Registration</title>
		
		<link rel="stylesheet" href="./styles.css">
	    <script>
		function validateForm() {
            var form = document.forms["registrationForm"];
            
            // validating ID
            var id = form["id"].value;
            if (!/^[0-9]{8}$/.test(id)) {
                alert("ID must be 8 digits.");
                return false;
            }

            // validating First Name
            var firstName = form["firstName"].value;
            if (!/^[a-zA-Z]+$/.test(firstName)) {
                alert("First name can only contain letters.");
                return false;
            }

            // validating Last Name
            var lastName = form["lastName"].value;
            if (!/^[a-zA-Z]+$/.test(lastName)) {
                alert("Last name can only contain letters.");
                return false;
            }

            // validating Project Title
            var projectTitle = form["projectTitle"].value;
            if (!/^[a-zA-Z-' ]*$/.test(projectTitle)) {
                alert("Project title can only contain letters, hyphens, apostrophes, and spaces.");
                return false;
            }

            // validating Email Address
            var emailAddress = form["emailAddress"].value;
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (!emailPattern.test(emailAddress)) {
                alert("Invalid email address format.");
                return false;
            }

            // validating Phone Number
            var phoneNumber = form["phoneNumber"].value;
            if (!/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/.test(phoneNumber)) {
                alert("Phone number format must be 012-345-6789.");
                return false;
            }

            // validating Time Slot
            var timeSlot = form["timeSlot"].value;
            if (timeSlot === "blank") {
                alert("Booking a seat for a time slot is required.");
                return false;
            }

            return true;
        }
    </script>
	
		
	</head>

	<body>
	
		<!-- for the MySQL database -->
		<?php
			 
			
			// local testing
			$servername = "your-server-name-here";	
			$username = "your-username-here";
			$password = "your-password-here";
			$dbname = "StudentRegistrationDB";
			
			// Creating connection
			$conn = new mysqli($servername, $username, $password);
			
			// Checking connection (Object Oriented Style)
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);	
			}
			
			
			// Creating the database (COMMENT AFTER DB IS CREATED FIRST TIME)
			$sql = "CREATE DATABASE IF NOT EXISTS StudentRegistrationDB";
			if ($conn->query($sql) === FALSE) {
				echo "Error creating database: " . $conn->error;
			}
			
			// Select the database
			$conn->select_db($dbname);

			
			// SQL to create table for students registered (COMMENT AFTER TABLE IS CREATED FIRST TIME)
			$sql2 = "CREATE TABLE IF NOT EXISTS Students (
				id INT(8) PRIMARY KEY,
				firstName VARCHAR(30) NOT NULL,
				lastName VARCHAR(30) NOT NULL,
				projectTitle VARCHAR(30) NOT NULL,
				emailAddress VARCHAR(80),
				phoneNumber VARCHAR(30),
				timeSlot VARCHAR(30) NOT NULL
			)";
			
			
			if ($conn->query($sql2) === FALSE) {
				echo "Error creating table" . $conn->error;
			}


			// SQL to create table for seats (COMMENT AFTER TABLE IS CREATED FIRST TIME)				
			$sql3 = "CREATE TABLE IF NOT EXISTS Seats (
				timeSlot VARCHAR(30) PRIMARY KEY,
				seatsLeft INT NOT NULL
			)";
			
			
			if ($conn->query($sql3) === FALSE) {
				echo "Error creating table" . $conn->error;
			}		
			
			// Check if Seats table is empty before inserting initial values
			$result = $conn->query("SELECT COUNT(*) AS count FROM Seats");
			$row = $result->fetch_assoc();
			if ($row['count'] == 0) {
				$slots = array(
					"7/15/24 4:00PM-5:00PM",
					"7/15/24 5:00PM-6:00PM",
					"7/15/24 6:00PM-7:00PM",
					"7/15/24 7:00PM-8:00PM",
					"7/15/24 8:00PM-9:00PM",
					"7/15/24 9:00PM-10:00PM"
				);

				// Initialize starting values for Seats table
				$stmt = $conn->prepare("INSERT INTO Seats(timeSlot, seatsLeft) VALUES(?, ?)");
				for ($i = 0; $i < count($slots); $i++) {
					$numSeatsLeft = 6;
					$stmt->bind_param("si", $slots[$i], $numSeatsLeft);
					$stmt->execute();
				}
				$stmt->close();
			}

		
		
			$id = $firstName = $lastName = $projectTitle = $emailAddress = $phoneNumber = $timeSlot = "";
			$idErr = $firstNameErr = $lastNameErr = $projectTitleErr = $emailAddressErr = $phoneNumberErr = $timeSlotErr = "";
						
			function getSeatsLeft($conn, $timeSlot) {
				$sql_select = "SELECT seatsLeft FROM Seats WHERE timeSlot = ?";
				
				$stmt = $conn->prepare($sql_select);
				
				$stmt->bind_param("s", $timeSlot);
				
				$stmt->execute();
				
				$stmt->bind_result($seatsLeft);
				
				$stmt->fetch();
				
				return $seatsLeft;
				
			}
			
			
			// decreasing number of seats when student registers for time slot
			function decreaseSeats($conn, $timeSlot) {
				$sql_update = "UPDATE Seats SET seatsLeft = (seatsLeft - 1) WHERE timeSlot = ?";
				$stmt = $conn->prepare($sql_update);
				if ($stmt === false) {
					die("Prepare failed: " . $conn->error);
				}
								
				$stmt->bind_param("s", $timeSlot);
				if (!$stmt->execute()) {
					echo "Error updating seats: " . $stmt->error;
				}
				$stmt->close();

			}
			
			
			// increasing number of seats when student registers switches between time slots
			function increaseSeats($conn, $id, $newTimeSlot) {
				// plan: 
				// first grab original time slot and temporarily store into variable
				// increase seat number by 1 for that variable
				// then update DB with replacing old section with new time slot section
				// decrease seat number for new section by 1
				
				
				// first, taking old time slot section and updating by increasing seats by 1
				$sql_select = "SELECT timeSlot FROM Students WHERE id = ?";
				$stmt = $conn->prepare($sql_select);
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$stmt->bind_result($prevTimeSlot);
				$stmt->fetch();
				$stmt->close();
								
				// increase
				$sql_update = "UPDATE Seats SET seatsLeft = (seatsLeft + 1) WHERE timeSlot = ?";
				
				$stmt = $conn->prepare($sql_update);
				
				$stmt->bind_param("s", $prevTimeSlot);
				
				$stmt->execute();

				$stmt->close();
				
				
				// now, updating ID's old time slot to new time slot section and decreasing seats by 1
				$sql_update = "UPDATE Students SET timeSlot = ? WHERE id = ?";
				
				$stmt = $conn->prepare($sql_update);
				$stmt->bind_param("si", $newTimeSlot, $id);
				$stmt->execute();
				$stmt->close();
				
				// decrease
				$sql_update = "UPDATE Seats SET seatsLeft = (seatsLeft - 1) WHERE timeSlot = ?";
				$stmt = $conn->prepare($sql_update);
				$stmt->bind_param("s", $newTimeSlot);
				$stmt->execute();
				$stmt->close();
				
				
			}
		
			
			
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				
				if (empty($_POST["id"])) {
					$idErr = "ID is required";
				}
				else {
					$id = (int)test_input($_POST["id"]);
					
					// the preg_match() fn searches through the string for patterns,
					// and returns TRUE if the pattern exists and false otherwise
					if (!preg_match("/^[0-9]{8}$/", $id)) {
						$idErr = "ID must be 8 digits.";
					}
					else {
						// query counts for number of rows that are equal to a specific ID value
						$sql = "SELECT COUNT(*) as count FROM Students WHERE id = ?";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("i", $id);
						$stmt->execute();
						$stmt->bind_result($count);
						$stmt->fetch();
						$stmt->close();
						// if there is more than 0 rows of a specific ID, that means ID already exists so duplicate ID cannot be added to DB
						if (($count > 0 && !($_POST["timeSlot"] == "blank") && getSeatsLeft($conn, $timeSlot) > 0) || ($count > 0 && !($_POST["timeSlot"] == "blank"))) {
							echo "<script>
								alert('You have been registered for a new section.');
							</script>";
							$idErr = 'ID already exists.';
							$timeSlot = test_input($_POST["timeSlot"]); // sanitize input
							increaseSeats($conn, $id, $timeSlot);	// timeSlot represents new timeSlot user wants
							
						}
					}
				}
				
				if (empty($_POST["firstName"])) {
					$firstNameErr = "First name is required";
				}
				else {
					$firstName = test_input($_POST["firstName"]);
					
					if (!preg_match("/^[a-zA-Z]+$/", $firstName)) {
						$firstNameErr = "Only letters are allowed";
					}
				}
				
				if (empty($_POST["lastName"])) {
					$lastNameErr = "Last name is required";
				}
				else {
					$lastName = test_input($_POST["lastName"]);
					
					if (!preg_match("/^[a-zA-Z]+$/", $lastName)) {
						$lastNameErr = "Only letters are allowed";
					}
				}
				
				if (empty($_POST["projectTitle"])) {
					$projectTitleErr = "Project title is required";
				}
				else {
					$projectTitle = test_input($_POST["projectTitle"]);
					
					if (!preg_match("/^[a-zA-Z-' ]*$/", $projectTitle)) {
						$projectTitleErr = "Only letters and white spaces are allowed";
					}
				}
				
				if (empty($_POST["emailAddress"])) {
					$emailAddressErr = "Email Address is required";
				}
				else {
					$emailAddress = test_input($_POST["emailAddress"]);
					
					// easiest way to validate email address
					if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
						$emailAddressErr = "Invalid email address format";
					}
				}
				
				if (empty($_POST["phoneNumber"])) {
					$phoneNumberErr = "Phone number is required";
				}
				else {
					$phoneNumber = test_input($_POST["phoneNumber"]);
					
					if (!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $phoneNumber)) {
						$phoneNumberErr = "Invalid phone number format";
					}
				}
				
				if ($_POST["timeSlot"] == "blank") {
					$timeSlotErr = "Booking a seat for a time slot is required";
				}
				else {
					$timeSlot = test_input($_POST["timeSlot"]);
					

					if (getSeatsLeft($conn, $timeSlot) == 0) {
						$timeSlotErr = "Sorry, this time slot is full. Please choose another slot.";
					}
					else {						
						// if statement for announcement of slot being full after last seat is taken
						if (getSeatsLeft($conn, $timeSlot) == 0) {
							echo "<script>alert('There are no more seats left in: $timeSlot');</script>";
						}	
					}			
					
				}
			}		


			function test_input($data) {
				$data = trim($data);
				// stripping unnecessary characters (extra space, tab, newlines)
				// from the user input data
				$data = stripslashes($data);
				// removing backslash (\) from the user input data
				$data = htmlspecialchars($data);
				return $data;
			}
			
			// inserting inputted values into table
			if (preg_match("/^[0-9]{8}$/", $id) && empty($idErr) && empty($firstNameErr) && empty($lastNameErr) && empty($projectTitleErr) && empty($emailAddressErr) && empty($phoneNumberErr) && empty($timeSlotErr)) {
				decreaseSeats($conn, $timeSlot);
				$stmt = $conn->prepare("INSERT INTO Students(id, firstName, lastName, projectTitle, emailAddress, phoneNumber, timeSlot) VALUES(?,?,?,?,?,?,?)");
				$stmt->bind_param("issssss", $id, $firstName, $lastName, $projectTitle, $emailAddress, $phoneNumber, $timeSlot);
				$stmt->execute();
				$stmt->close();
				echo "<script>alert('You have been registered!');</script>";
			}
			
		?>
	
		<h1>Student Registration Demo</h1>
		
		<button onclick="window.location.href='index.php'" class="button-design">Registration Form</button>
		<button onclick="window.location.href='index2.php'" class="button-design">Check Students Registered</button>
		<br><br>
				
		
		<form name="registrationForm" onsubmit="return validateForm();" method="post" action="<?php 
			echo htmlspecialchars($_SERVER["PHP_SELF"]);	// returns the page
		?>">
		    <h2>Student Registration Form</h2>
			<p><span class="error"><b>* required field</b></span></p>

			ID: <input type="text" name="id" value="<?php echo $id ?>">
			<span class="error">* <?php echo $idErr; ?></span>
			<br><br>
			First Name: <input type="text" name="firstName" value="<?php echo $firstName ?>">
			<span class="error">* <?php echo $firstNameErr; ?></span>
			<br><br>
			Last Name: <input type="text" name="lastName" value="<?php echo $lastName ?>">
			<span class="error">* <?php echo $lastNameErr; ?></span>			
			<br><br>
			Project Title: <input type="text" name="projectTitle" value="<?php echo $projectTitle ?>">
			<span class="error">* <?php echo $projectTitleErr; ?></span>			
			<br><br>			
			Email Address: <input type="text" name="emailAddress" value="<?php echo $emailAddress ?>">
			<span class="error">* <?php echo $emailAddressErr; ?></span>
			<br><br>
			Phone Number: <input type="text" name="phoneNumber" value="<?php echo $phoneNumber ?>" placeholder="012-345-6789">
			<span class="error">* <?php echo $phoneNumberErr; ?></span>
			<br><br>
			Time Slots Available: <br>
			<select id="timeSlot" name="timeSlot"> 
				<option value="blank"></option>
				<option <?php if (isset($timeSlot) && $timeSlot=="7/15/24 4:00PM-5:00PM") {echo "selected";} ?> value="7/15/24 4:00PM-5:00PM">7/15/24 4:00PM-5:00PM 
				<?php echo getSeatsLeft($conn, "7/15/24 4:00PM-5:00PM"); ?> seats remaining</option>
				<option <?php if (isset($timeSlot) && $timeSlot=="7/15/24 5:00PM-6:00PM") {echo "selected";} ?> value="7/15/24 5:00PM-6:00PM">7/15/24 5:00PM-6:00PM 
				<?php echo getSeatsLeft($conn, "7/15/24 5:00PM-6:00PM"); ?> seats remaining</option>
				<option <?php if (isset($timeSlot) && $timeSlot=="7/15/24 6:00PM-7:00PM") {echo "selected";} ?> value="7/15/24 6:00PM-7:00PM">7/15/24 6:00PM-7:00PM 
				<?php echo getSeatsLeft($conn, "7/15/24 6:00PM-7:00PM"); ?> seats remaining</option>
				<option <?php if (isset($timeSlot) && $timeSlot=="7/15/24 7:00PM-8:00PM") {echo "selected";} ?> value="7/15/24 7:00PM-8:00PM">7/15/24 7:00PM-8:00PM 
				<?php echo getSeatsLeft($conn, "7/15/24 7:00PM-8:00PM"); ?> seats remaining</option>
				<option <?php if (isset($timeSlot) && $timeSlot=="7/15/24 8:00PM-9:00PM") {echo "selected";} ?> value="7/15/24 8:00PM-9:00PM">7/15/24 8:00PM-9:00PM 
				<?php echo getSeatsLeft($conn, "7/15/24 8:00PM-9:00PM"); ?> seats remaining</option>
				<option <?php if (isset($timeSlot) && $timeSlot=="7/15/24 9:00PM-10:00PM") {echo "selected";} ?> value="7/15/24 9:00PM-10:00PM">7/15/24 9:00PM-10:00PM 
				<?php echo getSeatsLeft($conn, "7/15/24 9:00PM-10:00PM"); ?> seats remaining</option>
			</select>
			<span class="error">* <?php echo $timeSlotErr; ?></span>
			
			<br><br>
			<input type="submit" name="submit" value="Submit" class="submit-button">
		</form>
			
		<?php
				
			$conn->close();
		
		?>
		
		
		
	</body>

</html>
