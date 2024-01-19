<?php

// start session
session_start();
// config database
require_once 'config.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} ?>

    <div class="container" id="container-login">

        <h1 class="welcome-heading">Welcome to Workout Tracker</h1>
        <p class="welcome-subheading">Track your fitness journey and achieve your goals.</p>

        <?php if (isset($registerError)): ?>
            <p class="error"><?php echo $registerError; ?></p>
        <?php endif; ?>

        <!-- Header -->
        <h1>Register</h1>
        <!-- Display the register form -->
        <form class="block" method="post" onsubmit="return handleRegisterFormSubmit(event)">
            <div id="register-error-message" class="error hide"></div>

            <input class="input" type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <label for="email">Email:</label>
            <input class="input" type="email" name="email" id="email" required>
            
            <label for="username">Username:</label>
            <input class="input" type="text" name="username" id="username" required>
            
            <label for="password">Password:</label>
            <input class="input" type="password" name="password" id="password" required>

            <div class="progress">
                <div id="password-strength" 
                    class="progress-bar" 
                    role="progressbar" 
                    aria-valuenow="40" 
                    aria-valuemin="0" 
                    aria-valuemax="100" 
                    style="width:0%">
                </div>
            </div>

            <ul class="list-password-strength list-unstyled">
                <li class="low-upper-case">
                    <span>
                        &nbsp;Uppercase & Lowercase
                    </span>
                </li>
                <li class="one-number">
                    <span>
                        &nbsp;Number
                    </span> 
                </li>
                <li class="one-special-char">
                    <span >
                        &nbsp;Symbol (!@#$%^&*-)
                    </span>
                </li>
                <li class="eight-character">
                    <span >
                        &nbsp;Length > 8
                    </span>
                </li>
            </ul>
            <button class="btn-register" type="submit" name="register" value="Register">Register</button>
        </form>
    </div>

    

<style>
    .btn-hamburger {
        opacity: 0;
    }

    .progress {
        position: relative;
        height: 3px !important;
        background: gray;
        margin: .25rem 0;
    }

    #password-strength {
        height: 100%;
    }
    
    .progress-bar-danger {
        background-color: #e90f10;
    }

    .progress-bar-warning {
        background-color: #ffad00;
    }

    .progress-bar-success {
        background-color: #02b502;
    }

    .list-password-strength {
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
        justify-content: space-between;
        gap: 1rem; /* add some gap between the columns */
        padding: 0 .5rem;
    }

    .list-password-strength li {
        justify-content: flex-start;
        width: auto;
        /* display: inline-block; Make the list items inline-block */
        white-space: nowrap;
        flex: 0;
    }

    .list-password-strength li::before {
        content: "•"; /* Add bullet point symbol */
        font-size: 1rem; /* Increase font size */
        font-weight: bold; /* Make it thicker */
        margin-right: .5rem;
    }

    .list-password-strength li span{
        text-align: left;
        display: flex;
        justify-content: flex-start;
    }

    .list-password-strength li.met::before {
        content: url('img/checkmark.svg'); /* Change to checkmark when requirement is met */
        margin-right: .5rem;
        height: 1.2em;
        width: 1.2em;
        display: inline-block;
        vertical-align: middle;
        fill: green;
        overflow: hidden;
    }

    .path-checkmark {
        fill: green;
        stroke: green;
        stroke-width: 10px; /* adjust thickness as needed */
    }

</style>

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

<script>
    let state = false;
    let password = document.getElementById("password");
    let passwordStrength = document.getElementById("password-strength");
    let lowUpperCase = document.querySelector(".low-upper-case");
    let number = document.querySelector(".one-number");
    let specialChar = document.querySelector(".one-special-char");
    let eightChar = document.querySelector(".eight-character");

    password.addEventListener("keyup", function(){
        let pass = document.getElementById("password").value;
        checkStrength(pass);
    });

    function checkStrength(password) {
        let strength = 0;

        //If password contains both lower and uppercase characters
        if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
            strength += 1;
            lowUpperCase.classList.add('met');
        } else {
            lowUpperCase.classList.remove('met')
        }
        //If it has numbers and characters
        if (password.match(/([0-9])/)) {
            strength += 1;
            number.classList.add('met');
        } else {
            number.classList.remove('met');
        }
        //If it has one special character
        if (password.match(/([!,%,&,@,#,$,^,*,?,_,~,-])/)) {
            strength += 1;
            specialChar.classList.add('met');
        } else {
            specialChar.classList.remove('met');
        }
        //If password is greater than 7
        if (password.length > 7) {
            strength += 1;
            eightChar.classList.add('met');
        } else {
            eightChar.classList.remove('met');   
        }

        // If value is less than 2
        if (strength == 0) {
            passwordStrength.classList.remove('progress-bar-warning');
            passwordStrength.classList.remove('progress-bar-success');
            passwordStrength.classList.add('progress-bar-danger');
            passwordStrength.style = 'width: 10%';
            } else if (strength == 1) {
            passwordStrength.classList.remove('progress-bar-success');
            passwordStrength.classList.remove('progress-bar-danger');
            passwordStrength.classList.add('progress-bar-danger');
            passwordStrength.style = 'width: 30%';
        } else if (strength == 2) {
            passwordStrength.classList.remove('progress-bar-success');
            passwordStrength.classList.remove('progress-bar-danger');
            passwordStrength.classList.add('progress-bar-danger');
            passwordStrength.style = 'width: 50%';
        } else if (strength == 3) {
            passwordStrength.classList.remove('progress-bar-success');
            passwordStrength.classList.remove('progress-bar-danger');
            passwordStrength.classList.add('progress-bar-warning');
            passwordStrength.style = 'width: 70%';
        } else if (strength == 4) {
            passwordStrength.classList.remove('progress-bar-warning');
            passwordStrength.classList.remove('progress-bar-danger');
            passwordStrength.classList.add('progress-bar-success');
            passwordStrength.style = 'width: 100%';
        }
    }
</script>

