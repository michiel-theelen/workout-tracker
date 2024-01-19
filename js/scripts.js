function toggleCollapse(button) {
  const $button = $(button);
  // Only toggle classes if contentEditable is false
  if ($button.attr('contenteditable') === 'false') {
    const exerciseId = $button.data('exercise-id');
    const setList = document.querySelector(`ul.set-list[data-exercise-id="${exerciseId}"]`);
    button.classList.toggle('collapsed');
    const isCollapsed = button.classList.contains('collapsed');
    if (isCollapsed) {
      setList.classList.add('collapsed');
    } else {
      setList.classList.remove('collapsed');
    }
  }
}

function chartChangeExercise(select) {
  // Get selected value
  var selectedValue = select.value;
  // Update the 'seriesSelector' element's value
  document.getElementById('seriesSelector').value = selectedValue;
  // Update the 'dataSelector' element's value
  document.getElementById('dataSelector').value = 'all';
  updateChart();
}

window.onload = function() {
    document.getElementById('select-exercise').addEventListener('change', function (event) {
    // Get the value of the selected option in the 'select-exercise' element
    const selectedValue = event.target.value;

    // Update the 'seriesSelector' element's value
    document.getElementById('seriesSelector').value = selectedValue;
    // Update the 'dataSelector' element's value
    document.getElementById('dataSelector').value = 'all';
    
    initChart();

  });
};

function downloadCalendar() {
  const link = document.createElement('a');
  link.href = 'workout_calendar.ics';
  link.download = 'workout_calendar.ics';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function loadPage(pageName) {

  const mainElement = document.querySelector('main');
  var container = mainElement.querySelector('.container');

  if (container) {
    container.classList.add('hide-right');
  }
  
  let cssVariables = getComputedStyle(document.documentElement);
  let navTransitionDuration = cssVariables.getPropertyValue('--nav-transition-duration').trim();
  let navTransitionDurationMilliseconds = parseFloat(navTransitionDuration) * 1000;

  let containerTransitionDuration = cssVariables.getPropertyValue('--container-transition-duration').trim();
  let containerTransitionDurationMilliseconds = parseFloat(containerTransitionDuration) * 1000;
   
  setTimeout(function() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', pageName, true);
    xhr.onload = function() {
      if (this.status === 200) {
        
        window.scrollTo(0, 0);

        document.querySelector('main').innerHTML = this.responseText;
        var containerTracker = mainElement.querySelector('.container');
        containerTracker.classList.add('hide-left');
        
        setTimeout(function() {
          containerTracker.classList.remove('hide-left');

          if (document.getElementById('container-tracker')){
            initChart();
          } else if (document.getElementById('container-stopwatch')){
            initStopwatchChart();
          }

          // setTimeout(function() {
            hideMenu();
          // }, containerTransitionDurationMilliseconds);
        }, containerTransitionDurationMilliseconds);
      }
    }
    xhr.send();
  }, containerTransitionDurationMilliseconds);
}

function logout() {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'logout.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function() {
    if (xhr.status === 200) {
      loadPage('content_index.php');
      console.log('Logged out successfully.');
    } else {
      console.error('Logout failed:', this.responseText);
      // handle errors here
    }
  };
  xhr.send();
}

function login(username, password, csrfToken, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'login.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status === 200 && this.responseText.trim() === 'success') {
            callback(null);
        } else {
            callback(this.responseText);
        }
    };
    xhr.send(`username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&csrf_token=${encodeURIComponent(csrfToken)}`);
}

function handleLoginFormSubmit(event) {
    event.preventDefault();
    const username = event.target.username.value;
    const password = event.target.password.value;
    const csrfToken = document.querySelector("input[name='csrf_token']").value;
    login(username, password, csrfToken, function(error) {
        const errorMessageElement = document.getElementById('login-error-message');
        if (errorMessageElement) {
            if (error) {
                errorMessageElement.textContent = error;
                errorMessageElement.classList.remove('hide');
            } else {
                errorMessageElement.textContent = '';
                errorMessageElement.classList.add('hide');
            }
        }
        if (!error) {
            loadPage('content_index.php');
        }
    });
    return false;
}

function register(email, username, password, csrfToken, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'register.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status === 200 && this.responseText.trim() === 'success') {
          console.log('registration succesful');
          callback(null);
        } else {
          console.log('registration failed');
          callback(this.responseText);
        }
    };
    xhr.send(`email=${encodeURIComponent(email)}&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&csrf_token=${encodeURIComponent(csrfToken)}&register=register`);

}

function handleRegisterFormSubmit(event) {
    event.preventDefault();
    const csrfToken = document.querySelector("input[name='csrf_token']").value;
    const email = event.target.email.value;
    const username = event.target.username.value;
    const password = event.target.password.value;
    register(email, username, password, csrfToken, function(error) {
        const errorMessageElement = document.getElementById('register-error-message');
        if (errorMessageElement) {
            if (error) {
                errorMessageElement.textContent = error;
                errorMessageElement.classList.remove('hide');
            } else {
                errorMessageElement.textContent = '';
                errorMessageElement.classList.add('hide');
            }
        }
        if (!error) {
          loadPage('content_index.php'); 
        }
    });
    return false;
}

function hideMenu() {   
  let cssVariables = getComputedStyle(document.documentElement);
  let navTransitionDuration = cssVariables.getPropertyValue('--nav-transition-duration').trim();
  let navTransitionDurationSeconds = parseFloat(navTransitionDuration);
  let navTransitionDurationMilliseconds = navTransitionDurationSeconds * 1000;

  let containerTransitionDuration = cssVariables.getPropertyValue('--container-transition-duration').trim();
  let containerTransitionDurationSeconds = parseFloat(containerTransitionDuration);
  let containerTransitionDurationMilliseconds = containerTransitionDurationSeconds * 1000;

  // check if the click target is outside 
  const hamburgerButton = document.querySelector('.btn-hamburger');
  const sideMenu = document.querySelector('.side-menu');
  var content = document.querySelector('main');
  var html = document.querySelector('html');

  setTimeout(function() {
      hamburgerButton.classList.remove('is-active');
      sideMenu.classList.remove('show'); 
  }, 0);

  setTimeout(function() {
      sideMenu.classList.add('hide');
      html.classList.remove('no-scroll');
  }, navTransitionDurationMilliseconds);

};

function toggleMenu(button) { 
  // Load transition duration
  let cssVariables = getComputedStyle(document.documentElement);
  let navTransitionDuration = cssVariables.getPropertyValue('--nav-transition-duration').trim();
  let navTransitionDurationMilliseconds = parseFloat(navTransitionDuration) * 1000;
  
  console.log(navTransitionDuration);
  const sideMenu = document.getElementById('side-menu');
  const main = document.querySelector('main');
  const html = document.querySelector('html');

  if (sideMenu.classList.contains('hide')) {
    sideMenu.classList.toggle('hide');
  } else {
    setTimeout(function() {
      sideMenu.classList.toggle('hide');
    }, navTransitionDurationMilliseconds + 10);
  }
  setTimeout(function() {
    html.classList.toggle('no-scroll');
    button.classList.toggle('is-active');
    sideMenu.classList.toggle('show');
  }, 10);
};

document.addEventListener('click', function(event) {
  const sideMenu = document.querySelector('.side-menu');
  const navButtons = document.querySelectorAll('.btn-nav');
  const hamburgerButton = document.querySelector('.btn-hamburger');
  
  let isClickInsideNav = Array.from(navButtons).some(navButton => navButton == event.target || navButton.contains(event.target));
  
  if (
    sideMenu.classList.contains('show') &&
    !isClickInsideNav &&  // Updated condition
    !hamburgerButton.contains(event.target) 
  ) {
    console.log(event.target)
    hideMenu();
  }
});

  



