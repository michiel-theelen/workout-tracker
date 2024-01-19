<div class="container" id="container-stopwatch">
  <h2>Stopwatch</h2>
  <div class="block" id="block-stopwatch">
    <div id="stopwatch">
      <span class="time-span" id="hours">00</span>
      <span>:</span>
      <span class="time-span" id="minutes">00</span>
      <span>:</span>
      <span class="time-span" id="seconds">00</span>
      <span>,</span>
      <span class="time-span" id="milliseconds">00</span>
    </div>
    <ul id="ul-stopwatch-stats">
      <li>
        <div>
          <span>Fastest</span>
          <span id="li-fastest">00:00:00,00</span>
        </div>
        <div>
          <span>Slowest</span>
          <span id="li-slowest">00:00:00,00</span>
        </div>
        <div>
          <span>Average</span>
          <span id="li-average">00:00:00,00</span>
        </div>
      </li>
    </ul>
    <div id="container-btn-stopwatch">
    <button class="btn-start" id="btn-stopwatch-start-stop"></button>
    <button class="btn-reset" id="btn-stopwatch-lap-reset"></button>
  </div>
  </div>

  <!-- <div class="switch-container">
    <label class="switch">
      <input type="checkbox" id="reverse-order">
      <span class="slider round"></span>
    </label>
    <label id="switch-text" for="reverse-order">Reverse order</label>
  </div> -->

  <!-- Add the lap button that will be pressed via speech recognition
  <div id="speech-recognition-container">
    <button id="btn-speech-recognition" onclick="toggleSpeechRecognition()">Start Speech Recognition</button>
    <div id="recognized-text"></div>
  </div> -->

  <div id="block-stopwatch-laps">
    <div class="ul-stopwatch-header" id="ul-stopwatch-header">
      <div class="lap-header">Lap No.</div>
      <div class="lap-header">Split</div>
      <div class="lap-header">Total</div>
    </div>
    <ul id="ul-stopwatch-laps">
      <li>
        <span id="li-lapno">00:00:00,00</span>
        <span id="li-split">00:00:00,00</span>
        <span id="li-total">00:00:00,00</span>
      </li>
    </ul>
  </div>
  <div class="block" id="block-stopwatch-chart" style="display:none;">
    <svg id="lap-chart"></svg>
  </div>
</div>

<script>
 
  </script>