<?php
// start sessions
session_start();
// config database
require_once 'config.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    
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
}
?>

            <div class="container" id="container-tracker">
                <!-- <h1>Workout Tracker</h1> -->
                <div class="sets-container">
                    <section>
                        <h2>Add set</h2>
                        <form  class="block" id="form-add-set" method="post" onsubmit="addSet(event)">
                            <!-- exercise -->
                            <div class="select-exercise-container">
                                <label for="name" >Exercise:</label>
                                <input type="text" list="select-exercise" id="name" name="name" required/>
                                <datalist aria-label=".form-select-sm" id="select-exercise" name="select-exercise" >
                                    <?php foreach ($exercises as $i => $exercise): ?>
                                        <option class="exercise-option" id="exercise-option-<?php echo $exercise['id']; ?>" value="<?php echo $exercise['name']; ?>" <?php echo ($i === 0) ? 'selected="selected"' : ''; ?>><?php echo $exercise['name']; ?></option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>

                            <div class="set-data-container">
                                <!-- repetitions -->
                                <div class="s">
                                    <label class="" for="reps">Reps:</label>
                                    <input type="number" class="input exercise" id="reps" name="reps" pattern="[0-9]*" inputmode="numeric" required>
                                </div>
                                <!-- weight -->
                                <div class="">
                                    <label class="" for="weight">Weight:</label>
                                    <input type="number" class="input exercise" id="weight" name="weight" pattern="[0-9,]*" inputmode="decimal" required>
                                    </div>
                                <!-- date -->
                                <div class="date-container">
                                    <label class="" for="date">Date:</label>
                                    <input type="date" class="input exercise select-date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <!-- submit buttom -->
                                <button type="submit" class="btn-add" id="btn-add-set"></button>
                            </div>
                        </form>

                    </section>
                    
                
                    <?php

// Create an associative array of exercise id and exercise name
$exerciseNames = array_column($exercises, 'name', 'id');

// Sort sets by date in descending order
usort($sets, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Take the first 10 sets (or less if there are fewer than 10 sets)
$recentSets = array_slice($sets, 0, 10);
?>

<section>
    <h2>Recent Sets</h2>
    <div class="block" id="recentSetsContainer">
        <?php foreach ($recentSets as $set): ?>
            <ul class="recent-set-item">
                <?php
                    $exerciseName = $exerciseNames[$set['exercise_id']];
                    echo '<span>' . $exerciseName . '</span></span>' . $set['reps'] . ' x ' . $set['weight'] . ' kg</span>';
                ?>
            </ul>
        <?php endforeach; ?>
    </div>
</section>

                </div>

                <!-- chart section -->
                <section id="section-chart">
                    <h2>Progression</h2>
                    <div class="block" style="padding:0">
                            <div class="chart-container" id="chart-container">
                            <!-- data -->
                            <div class="chart" id="chart" onload="initChart()">
                                <!-- controls -->   
                                <div class="control-container">
                                    <!-- scroll left -->
                                    <!-- <button id="scrollLeft" class="btn-scroll btn-scroll-left" onclick="chartScrollLeft()"></button> -->
                                    <!-- select time range -->
                                    <div class="select">
                                        <select id="dateRangeSelector" onchange="chartSelectDateRange()">
                                            <option value="1w">Week</option>
                                            <option value="1m">Maand</option>
                                            <option value="1y">Jaar</option>
                                            <option value="all" selected>Alles</option>
                                        </select>
                                    </div>
                                    <!-- select exercise -->
                                    <div class="select">
                                        <select id="exerciseSelector" onchange="updateChart()">
                                            <option value="all" selected>Alles</option>
                                            <?php foreach ($exercises as $exercise): ?>
                                                <?php $exerciseId = $exercise['id']; ?>
                                                <option value="<?php echo $exerciseId; ?>"><?php echo $exercise['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- Select data metric -->
                                    <div class="select">
                                        <select id="dataSelector" onchange="updateChart()">
                                            <option value="all" selected>Alles</option>
                                            <option value="max">Zwaarste</option>
                                            <option value="total">Totaal</option>
                                        </select>
                                    </div>
                                    <!-- scroll right -->
                                    <!-- <button id="scrollRight" class="btn-scroll btn-scroll-right" onclick="chartScrollRight()"></button> -->
                                    <!-- fullscreen -->
                                    <button class="btn-fullscreen" id="btn-fullscreen" onclick="chartFullScreen()"></button> 
                                </div>   
                            </div>
                            <!-- legend -->
                            <div class="chart-legend"></div> 
                        </div>     
                        </div>
                                            </div>
                </section>
            </div>
        </main>
    

        <style>
            #date-slider {
    width: 100%;
    height: 25px;
    background: #d3d3d3;
    outline: none;
    opacity: 0.7;
    transition: opacity .2s;
    border-radius: 12px;
}

#date-slider:hover {
    opacity: 1;
}

#date-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 25px;
    height: 25px;
    background: #4CAF50;
    cursor: pointer;
    border-radius: 50%;
}

#date-slider::-moz-range-thumb {
    width: 25px;
    height: 25px;
    background: #4CAF50;
    cursor: pointer;
    border-radius: 50%;
}

#slider-date-display {
    text-align: center;
    margin-top: 20px;
    font-size: 20px;
}
</style>

<script>
    function chartChangeExercise(selectElement) {
        var customInput = document.getElementById('custom-exercise-input');

        // Show/hide the custom exercise input based on the selection
        customInput.style.display = (selectElement.value === 'custom') ? 'block' : 'none';
    }
</script>