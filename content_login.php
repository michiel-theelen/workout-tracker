<?php
// start session
session_start();
// config database
require_once 'config.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} ?>

<!-- Display the error Message -->
<?php if (isset($error)): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>

<div class="container" id="container-login">
    <!-- Header -->
    <h1>Login</h1>
    <!-- Display the login form -->
    <form class="block" d="form-login" method="post" onsubmit="return handleLoginFormSubmit(event)">
        <div id="login-error-message" class="error hide"></div>

        <input class="input" type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <label for="username">Username:</label>
        <input class="input" type="text" name="username" id="input-username" required>
        
        <label for="password">Password:</label>
        <input class="input" type="password" name="password" id="input-password" required>
        
        <button class="btn-register" id="btn-login" type="submit" name="login" value="Login">Login</button>

        <div>Forgot your password?<u>Click here</u></div>
        <div>Don't have an account yet?&nbsp;<u style="cursor: pointer" onclick="loadPage('content_register.php')"> Register</u></div>
    </form>

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
