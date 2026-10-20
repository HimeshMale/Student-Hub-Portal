<?php
require_once __DIR__ . "/includes/session.php";
requireRole("student");   // only logged-in students get past this line
?>
<!doctype html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Hub Portal</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: Arial, sans-serif;
      }

      header table {
        margin: 20px auto;
      }

      header td {
        vertical-align: middle;
        padding: 0 10px;
      }

      nav {
        text-align: center;
        margin-bottom: 20px;
      }

      nav a {
        margin: 0 10px;
        color: #4CAF50;
        text-decoration: none;
      }

      main {
        width: 80%;
        margin: 0 auto 20px auto;
        text-align: center;
      }

      table {
        border-collapse: collapse;
        width: 60%;
        margin: 0 auto 20px auto;
      }

      td {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
      }

      td a {
        color: #4CAF50;
        text-decoration: none;
      }

      footer {
        text-align: center;
        margin-top: 20px;
        padding: 10px;
        color: #555;
      }

      /* Dark mode button and theme */
      .top-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 8px 14px;
        border: none;
        border-radius: 4px;
        background-color: #4CAF50;
        color: #fff;
        cursor: pointer;
      }

      body.dark-mode {
        background-color: #1e1e1e;
        color: #eee;
      }

      body.dark-mode td {
        border-color: #555;
      }

      body.dark-mode footer {
        color: #aaa;
      }
    </style>
  </head>
  <body>
    <button id="darkBtn" class="top-btn">Dark Mode</button>
    <header>
      <table border="0">
        <tr>
          <td><img src="Images/logo.webp" alt="Student Hub Logo" width="100" height="100" /></td>
          <td><h1>Student Hub</h1></td>
        </tr>
      </table>
    </header>

    <nav>
      <a href="Attendance.php">Attendance</a>
      <a href="Contact.php">Contact</a>
      <a href="Details.php">Events</a>
      <a href="fees.php">Fees</a>
      <a href="material.php">Material</a>
      <a href="Profile.php">Profile</a>
      <a href="result.php">Result</a>
      <a href="Timetable.php">Timetable</a>
      <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION["username"]) ?>)</a>
    </nav>
    <main>
      <p>
        Welcome, <?= htmlspecialchars($_SESSION["username"]) ?>! This portal allows students to access
        academic information such as attendance, fees, study materials,
        timetable, events, and results.
      </p>
    </main>
    <table border="1">
      <tr>
        <td><a href="Attendance.php">Attendance</a></td>
        <td><a href="Timetable.php">Timetable</a></td>
        <td><a href="result.php">Results</a></td>
      </tr>
      <tr>
        <td><a href="fees.php">Fees</a></td>
        <td><a href="material.php">Material</a></td>
        <td><a href="Details.php">Upcoming Events</a></td>
      </tr>
    </table>
    <footer>
      <p>Student Hub Portal@2026</p>
    </footer>

    <script>
      const btn = document.getElementById("darkBtn");

      // Check saved preference when page loads
      if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
        btn.textContent = "Light Mode";
      }

      // Toggle on click and save preference
      btn.addEventListener("click", function () {
        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {
          btn.textContent = "Light Mode";
          localStorage.setItem("theme", "dark");
        } else {
          btn.textContent = "Dark Mode";
          localStorage.setItem("theme", "light");
        }
      });
    </script>
  </body>
</html>