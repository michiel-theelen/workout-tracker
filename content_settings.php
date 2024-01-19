<?php
// start session
session_start();
// config database
require_once 'config.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    
  require_once 'config.php';

  // Get user ID
  $user_id = $_SESSION['user_id'];
  
  //  Get exercises for user
  $exercises = $db->prepare("
      SELECT * 
      FROM exercises 
      WHERE user_id = :user_id 
      ORDER BY name ASC
  ");
  $exercises->execute([':user_id' => $user_id]);
  $exercises = $exercises->fetchAll(PDO::FETCH_ASSOC);

  // Get sets for user
  $sets = $db->prepare("
      SELECT * 
      FROM sets 
      WHERE user_id = :user_id 
      ORDER BY date ASC
  ");
  $sets->execute([':user_id' => $user_id]);
  $sets = $sets->fetchAll(PDO::FETCH_ASSOC);

  // Prepare data for the chart
  $data = [];
  foreach ($exercises as $exercise) {
      $data[$exercise['id']] = [
          'name' => $exercise['name'],
      ];
  }
  foreach ($sets as $set) {
      $data[$set['exercise_id']]['data'][] = [
          'id' => $set['id'],
          'date' => $set['date'],
          'weight' => (int) $set['weight'],
          'reps' => (int) $set['reps'] // Include 'reps' data in the JSON output
      ];   
  }

  // Fetch the username for the logged-in user
  $stmt = $db->prepare("SELECT username FROM users WHERE user_id = :user_id");
  $stmt->bindParam(':user_id', $_SESSION['user_id']);
  $stmt->execute();
  $username = $stmt->fetchColumn();

} else {

  header('Location: /workout-tracker/');
  exit();

}
?>

  <div class="container" id="container-settings">
    <section>
        <h2>Download data</h2>
        <div class="block downloads-container">
                <button class="btn-download btn-download-csv" id="btn-download-csv" title="Download CSV" onclick="downloadCsv()">Download Database</button>
                <button class="btn-download btn-download-ical" id="btn-download-ical" title="Download ICS" onclick="downloadCalendar()">Download Kalender</button>                   
        </div>
    </section>

    <!-- list sets -->
    <section>
        <h2>Edit sets</h2>
        <div class="block">
            <ul id="exercise-list">
            <?php foreach ($data as $exerciseId => $exercise):?>
                <li class="li-exercise" data-exercise-id="<?php echo $exerciseId; ?>">
                    <!-- exercise name -->
                    <span class="exercise-name collapsed" contenteditable="false" data-exercise-id="<?php echo $exerciseId; ?>" onclick="toggleCollapse(this)">
                        <?php echo $exercise['name']; ?>
                    </span>
                    <button class="btn-edit-exercise btn-edit" data-exercise-id="<?php echo $exerciseId; ?>" onclick="editExercise(this)"></button> <!-- edit exercise -->
                    <button class="btn-delete-exercise btn-delete" data-exercise-id="<?php echo $exerciseId; ?>" onclick="deleteExercise(this)"></button> <!-- delete exercise -->
                </li>
                <ul class="set-list collapsed" data-exercise-id="<?php echo $exerciseId; ?>">
                <?php foreach ($exercise['data'] as $set):
                $id = $set['id'];
                $date = $set['date'];
                $formattedDate = date('j F', strtotime($date));
                $weight = $set['weight'];
                $reps = $set['reps'];
                ?>
                <li class="li-set" data-set-id="<?php echo $id; ?>" data-exercise-id="<?php echo $exerciseId; ?>" data-exercise-name="<?php echo $exercise['name']; ?>">
                    <span class="set-info">
                        <p><?php echo $formattedDate ?></p> <p><?php echo $reps; ?>x<?php echo $weight; ?>kg</p>
                    </span>
                    <button class="btn-delete-set btn-delete" data-set-id="<?php echo $id; ?>" onclick="deleteSet(this)"></button>      
                </li>
                <?php endforeach;?>
                </ul>  
            <?php endforeach; ?>
            </ul>
        </div>
    </section>


    <!-- Change Email Section -->
    <h2>Change email</h2>
    <div class="block">
      <form id="change-email-form" class="change-form" method="POST" onSubmit="updateEmail(event)">
          <label for="new-email">New email address:</label>
          <input class="input" type="email" id="input-new-email" name="new-email" autocomplete="off" required>
          <label for="current-password">Current password:</label>
          <input class="input" type="password" id="input-current-password" name="current-password" autocomplete="off" required>
          <button class="btn-edit btn-edit-email" type="button">Save email</button>
      </form>
    </div>
    <!-- Change Username Section -->
    <h2>Change username</h2>
    <div class="block">
      <form id="change-username-form" class="change-form" method="POST" onSubmit="updateUsername(event)">
          <label for="new-username">New name:</label>
          <input class="input" type="text" id="input-new-username" name="new-username" autocomplete="off" required>
          <label for="current-password">Current password:</label>
          <input class="input" type="password" id="input-current-password" name="current-password" autocomplete="off" required>
          <button class="btn-edit btn-edit-username" type="button">Save username</button>
      </form>
    </div>
    <!-- Change Password Section -->
    <h2>Change password</h2>
    <div class="block">
      <form id="change-password-form" class="change-form" method="POST" onSubmit="updatePassword(event)">
          <label for="old-password">Current password:</label>
          <input class="input" type="password" id="input-old-password" name="old-password" autocomplete="off" required>
          <label for="new-password1">New password:</label>
          <input class="input" type="password" id="input-new-password1" name="new-password1" autocomplete="off" required>
          <label for="new-password2">Confirm new password:</label>
          <input class="input" type="password" id="input-new-password2" name="new-password2" autocomplete="off" required>
          <button class="btn-edit btn-edit-username" type="button">Save new password</button>
      </form>
    </div>

  </div>
</main>

<script>
  function updateEmail(event) {
    event.preventDefault();
    let form = event.target;
    let formData = new FormData(form);
    fetch('update_email.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
      // Display result message to the user
    })
    .catch(error => console.error('Error:', error));
  }

  function updateUsername(event) {
    event.preventDefault();
    let form = event.target;
    let formData = new FormData(form);
    fetch('update_username.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
      // Display result message to the user
    })
    .catch(error => console.error('Error:', error));
  }

  function updatePassword(event) {
    event.preventDefault();
    let form = event.target;
    let formData = new FormData(form);
    fetch('update_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
      // Display result message to the user
    })
    .catch(error => console.error('Error:', error));
  }
</script>
