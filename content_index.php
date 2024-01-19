<?php
// start session
session_start();
// config database
require_once 'config.php';

// Load tracker if logged in
if (isset($_SESSION['user_id'])) {
    include('content_tracker.php');

// If not, show welcome page
} else { ?>


          <div class="container" id="container-welcome">
            <h1 class="welcome-heading">Welcome to Workout Tracker</h1>
            <p class="welcome-subheading">Track your fitness journey and achieve your goals.</p>

            <button class="btn-login" id="btn-login" onclick="loadPage('content_login.php')">Login</button>
            <button class="btn-register" id="btn-register" onclick="loadPage('content_register.php')">Register</button>
          </div>

  <style>

  nav {
    display: none !important;
  }

  .welcome-heading {
    font-size: 2rem;
    font-weight: 900;
    margin-bottom: -1rem;
    text-align: left;
    color: var(--select-arrow);
  }

  .welcome-subheading {
    font-size: 1.4rem;
    text-align: left;
  }

  .btn-hamburger{
    opacity: 0;
  }
  </style>



<?php }
?>
  

