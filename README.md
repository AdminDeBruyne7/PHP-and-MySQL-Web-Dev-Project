# Movie Ticket Booking System
My Movie Ticket Booking System is a web-based application developed using HTML, CSS, JavaScript, and PHP. The system is designed to provide users with a simple and interactive way to browse available movies, select showtimes, choose tickets, and submit their booking information.

The project was developed as an opportunity to apply both front-end and back-end web development concepts in a single system. It combines a responsive user interface with server-side PHP processing and database interactions to handle user input and ticket bookings.

Technologies Used

Frontend

HTML
CSS3
JavaScript

Backend

PHP

Database

MySQL

Development Tools

Visual Studio Code
XAMPP / Local PHP

Challenges Faced and Solutions
1. Laptop Failure during development -
   Solution: Rebuilt the PHP/MySQL backend against your existing HTML/JS frontend from scratch on a new machine, using XAMPP to get Apache + MySQL running quickly
   
3. Keeping Client-side and server-side validation in sync -
   Duplicated the core validation (name required, valid non-negative age) inside process_booking.php itself, so the server never inserts bad data even if JS is bypassed
   
5. Preventing SQL Injection -
   Split into two related tables — bookings and children — linked by a foreign key (booking_id), rather than cramming children into one column

