# Student-Registration-Demo
This project is a student registration system for a web technology class, designed to organize project demonstrations. The system allows students to sign up for one of six available one-hour time slots, with a maximum of six students per slot. The registration data is stored in a MySQL database, and the system ensures real-time interaction between the webpage and the server to keep track of available seats and student registrations.

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Steps to Run the Project](#steps-to-run-the-project)
- [Files Included](#files-included)

## Features
* Student Registration: Students can register by submitting their ID, first name, last name, project title, email address, phone number, and selected time slot.
* Real-time Seat Availability: The page displays the number of remaining seats for each time slot and blocks fully booked slots.
* Data Validation: The system validates all input fields before submission.
* Unique ID Check: Each student is uniquely identified by their ID. The system checks if a student is already registered and allows them to update their registration.
* Database Interaction: The submitted data is stored in a MySQL database, and the system interacts with the database to manage seat availability and student registrations.
* Student List Display: A separate webpage displays the list of registered students.

## Installation
To run this project locally, you will need XAMPP and MySQL Workbench. Follow the links below to download them:
* [XAMPP](https://www.apachefriends.org/download.html)
* [MySQL Workbench](https://dev.mysql.com/downloads/workbench/)

## Steps to Run the Project
1. **Set Up XAMPP and MySQL Workbench**
* Download and install XAMPP & MySQL Workbench.
* Start Apache and MySQL from the XAMPP control panel.
* Create a new connection in MySQL workbench and take note of the username and password you set.
2. **Configure PHP Script:**
* Save the provided code.
* Place these files in the `htdocs` directory of your XAMPP installation (e.g., `C:\xampp\htdocs\`).
3. **Access the Registration Page:**
* Open a web browser and go to `http://localhost/index.php`.
* You should see the student registration form and the list of available time slots.
4. **Register a Student:**
* Fill out the form with the required information and submit it.
* The system will validate the input, check for seat availability, and store the data in the database.
5. **View Registered Students:**
* Use the link provided on the registration page to view the list of registered students.

## Files included
* `index.html`: Main registration form where students can input their details to register for a project demonstration time slot.
* `index2.html`: Page displaying the list of registered students with their details, along with buttons to navigate to the registration form and this list.
* `styles.css`: Stylesheet that provides the design and layout for the HTML pages.
