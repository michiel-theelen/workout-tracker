async function addSet(event) {
  event.preventDefault();

  const exerciseName = document.getElementById('name').value;
  const date = document.getElementById('date').value;
  const reps = document.getElementById('reps').value;
  const weight = document.getElementById('weight').value;

  try {
    // Add the exercise (or get its ID if it already exists)
    const response = await fetch('add_exercise.php', {
      method: 'POST',
      body: new URLSearchParams({ name: exerciseName }),
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
    });

    const responseData = await response.json();

    if (response.ok) {
      const exerciseId = responseData.id;
      console.log('Exercise ID:', exerciseId);

      // Now, add the set using the obtained exercise ID
      const formData = new FormData();
      formData.append('exerciseId', exerciseId);
      formData.append('date', date);
      formData.append('reps', reps);
      formData.append('weight', weight);

      const addSetResponse = await fetch('add_set.php', {
        method: 'POST',
        body: formData,
      });

      if (addSetResponse.ok) {
        // add feedback element
        const addButton = document.getElementById('btn-add-set');
        addButton.classList.add('btn-success');
        setTimeout(() => {
          addButton.classList.remove('btn-success');
        }, 300);

        chartAddDataPoint();
      } else {
        throw new Error('Error adding set');
      }
    } else {
      throw new Error('Error adding exercise');
    }
  } catch (error) {
    console.error(error);
    alert('Error adding set');
  }
}

// delete set
function deleteSet(button) {
  confirm("Are you sure you want to delete this set?");
  const $button = $(button);
  const setId = $button.data('set-id');
  const setElement = $(`.li-set[data-set-id=${setId}]`);
  setElement.remove();
  fetch('delete_set.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({ id: setId })
  })
  .then(response => {
    if (response.ok) {
    } else {
      throw new Error('Error deleting set');
    }
  })
  .catch(error => {
    console.error('Error:', error);
  });
};

// delete exercise
function deleteExercise(button) {
  confirm("Are you sure you want to delete this exercise?");
  const $button = $(button);
  const exerciseId = $button.data('exercise-id');
  // remove li exercise
  const listElement = $button.closest('li');
  listElement.remove();
  // remove li sets
  $('.set-list li[data-exercise-id="' + exerciseId + '"]').remove();
    $.ajax({
      url: 'delete_exercise.php',
      method: 'POST',
      data: { id: exerciseId },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
        } else {
          alert('Error deleting exercise');
          location.reload(true);
        }
      },
      error: function(xhr, status, error) {
        alert('Error deleting exercise: ' + xhr.responseText);
        location.reload(true);
      }
    });
  return false;
};

// edit exercise
function editExercise(button) {
  const $button = $(button);
  const $exerciseName = $button.siblings('.exercise-name');
  const isEditing = $exerciseName.attr('contenteditable') === 'true';
  if (isEditing) {
    const exerciseId = $button.data('exercise-id');
    const newName = $exerciseName.text().trim();
    $.post('update_exercise.php', { id: exerciseId, name: newName }, function(response) {
      if (response) {
      } else {
        alert('Error updating exercise name.');
      }
    });
    // add feedback element
    button.classList.add('btn-success');
    setTimeout(() => {
      button.classList.remove('btn-success');
    }, 300);
    // show edit icon
    $exerciseName.attr('contenteditable', 'false');
    button.classList.add('btn-edit');
    button.classList.remove('btn-save');
  } else {
    $exerciseName.attr('contenteditable', 'true');
    button.classList.add('btn-save');
    button.classList.remove('btn-edit');
  }
  return false;
};

// update username
function updateUsername(event) {
  event.preventDefault();

  const formData = new FormData(document.getElementById('change-username-form'));

  // add feedback element
  const addButton = event.target;
  addButton.classList.add('btn-success');
  setTimeout(() => {
    addButton.classList.remove('btn-success');
  }, 300);

  fetch('update_username.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert(data.success);
    } else {
      alert(data.error);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error updating username');
  });
}
