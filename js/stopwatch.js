let stopwatchInterval;
let startTime;
let elapsedTime = 0;
let numLap = 1;
let lapTimes = [];

function startStopwatch() {

    // $('#ul-stopwatch-laps').html('');
    // Add the first lap when start is pressed

    if (lapTimes.length === 0) {
      console.log('Lap Added!');
      $('#ul-stopwatch-laps').html('');
      addLap();
    }

    startTime = Date.now() - elapsedTime;
    stopwatchInterval = setInterval(function updateStopwatch() {
      elapsedTime = Date.now() - startTime;
      
      let hours = Math.floor(elapsedTime / 3600000); // Convert milliseconds to hours
      let minutes = new Date(elapsedTime).getMinutes();
      let seconds = new Date(elapsedTime).getSeconds();
      let milliseconds = new Date(elapsedTime).getMilliseconds();

      $('.time-span#hours').text(hours.toString().padStart(2, "0"));
      $('.time-span#minutes').text(minutes.toString().padStart(2, "0"));
      $('.time-span#seconds').text(seconds.toString().padStart(2, "0"));
      $('.time-span#milliseconds').text(milliseconds.toString().slice(0, 2));

      // Update the lap time
      let currentLap = lapTimes[lapTimes.length - 1];
      currentLap.lapTime = elapsedTime - currentLap.totalTime;
      
      let lapId = "laptime-" + lapTimes.length;
      let totalId = "totaltime-" + lapTimes.length;
      console.log(lapId + totalId);
      document.getElementById(lapId).textContent = timeToString(currentLap.lapTime);
      document.getElementById(totalId).textContent = timeToString(elapsedTime);
    }, 10);
    $('#btn-stopwatch-start').text('Lap');
    $('#btn-stopwatch-reset').text('Stop');
    $('#btn-stopwatch-reset').prop('disabled', false);
}

// Convert ms to string hh:mm:ss,SS
function timeToString(time) {
  let diffInHours = time / 3600000;
  let hh = Math.floor(diffInHours);

  let diffInMinutes = (diffInHours - hh) * 60;
  let mm = Math.floor(diffInMinutes);

  let diffInSeconds = (diffInMinutes - mm) * 60;
  let ss = Math.floor(diffInSeconds);

  let diffInMilliseconds = (diffInSeconds - ss) * 100;
  let ms = Math.floor(diffInMilliseconds);

  let formattedHH = hh.toString().padStart(2, "0");
  let formattedMM = mm.toString().padStart(2, "0");
  let formattedSS = ss.toString().padStart(2, "0");
  let formattedMS = ms.toString().padStart(2, "0");

  return `${formattedHH}:${formattedMM}:${formattedSS},${formattedMS}`;
}


function stopStopwatch() {
    clearInterval(stopwatchInterval);
    $('#btn-stopwatch-start').text('Start');
    $('#btn-stopwatch-reset').text('Reset');
}

 // add this line to store the lap times
function addLap() {
    let totalLapTime = elapsedTime;
    let lastLapTime = (lapTimes.length > 0) ? lapTimes[lapTimes.length - 1].totalTime : 0;
    let currentLapTime = totalLapTime - lastLapTime;

    let lapDiv = document.createElement('li');
    lapDiv.id = 'lap-' + (lapTimes.length + 1);
    lapDiv.innerHTML = "<span>" + (lapTimes.length + 1) + "</span><span id='laptime-" + (lapTimes.length + 1) + "'></span><span id='totaltime-" + (lapTimes.length + 1) + "'></span>";

    // Add lap times
    let minLapTime = Math.min(...lapTimes.map(lap => lap.lapTime));
    let maxLapTime = Math.max(...lapTimes.map(lap => lap.lapTime));

    if (lapTimes) {
      lapTimes.forEach((lap, index) => {
        let lapId = 'lap-' + (index + 1);
        if (lap.lapTime === maxLapTime) {
            $('#' + lapId).addClass("slowest").removeClass("fastest");
        } else if (lap.lapTime === minLapTime) {
            $('#' + lapId).addClass("fastest").removeClass("slowest");
        } else {
            $('#' + lapId).removeClass("slowest fastest");
        }
      });
    }

    lapTimes.push({
        lapTime: currentLapTime,
        totalTime: totalLapTime
    });

    // updateStopwatchChart();
    $('#ul-stopwatch-laps').prepend(lapDiv);
    
    // Update the statistics
    const totalLaps = lapTimes.length;
    const totalTime = lapTimes.reduce((sum, lap) => sum + lap.lapTime, 0);
    const averageLapTime = totalTime / totalLaps;
    const fastestLapTime = Math.min(...lapTimes.map(lap => lap.lapTime));
    const slowestLapTime = Math.max(...lapTimes.map(lap => lap.lapTime));

    // Add the statistics to the end of the list
    $('#li-fastest').text(timeToString(fastestLapTime));
    $('#li-slowest').text(timeToString(slowestLapTime));
    $('#li-average').text(timeToString(averageLapTime));
}

// Update the resetStopwatch function to reset the lapTimes array
function resetStopwatch() {
    clearInterval(stopwatchInterval);
    elapsedTime = 0;
    numLap = 1;
    lapTimes = []; // reset lapTimes
    $('#hours').text('00');
        $('#minutes').text('00');
        $('#seconds').text('00');
        $('#milliseconds').text('00');
    $('#btn-stopwatch-start').text('Start');
    $('#btn-stopwatch-reset').text('Reset');
    $('#btn-stopwatch-reset').prop('disabled', true);
    $('#ul-stopwatch-laps').html('<li><span>00:00:00,00</span><span>00:00:00,00</span><span>00:00:00,00</span></li>');
    // Add the statistics to the end of the list
    $('#li-fastest').text("00:00:00,00");
    $('#li-slowest').text("00:00:00,00");
    $('#li-average').text("00:00:00,00");
    
    updateStopwatchChart()
}


let svgStopwatch, xScaleStopwatch, yScaleStopwatch, lineGenerator, areaGenerator;
function initStopwatchChart() {
  svgStopwatch = d3.select("#lap-chart");
  
  // Define scales
  xScaleStopwatch = d3.scaleLinear();
  yScaleStopwatch = d3.scaleLinear();

 // Define line generator
lineGenerator = d3.line()
  .x((d, i) => xScaleStopwatch(i))
  .y(d => yScaleStopwatch(d.lapTime));
    
// Define area generator
areaGenerator = d3.area()
  .x((d, i) => xScaleStopwatch(i))
  .y0(svgStopwatch.node().getBoundingClientRect().height)
  .y1(d => yScaleStopwatch(d.lapTime));

}

function updateStopwatchChart() {
  // Set the domain of the scales
  xScaleStopwatch.domain([0, lapTimes.length - 1]).range([0, svgStopwatch.node().getBoundingClientRect().width]);
  yScaleStopwatch.domain(d3.extent(lapTimes, d => d.lapTime)).range([svgStopwatch.node().getBoundingClientRect().height, 0]);

  // Create or update the line
  svgStopwatch.selectAll(".lap-line")
    .data([lapTimes])
    .join("path")
    .attr("class", "lap-line")
    .attr("d", lineGenerator)
    .attr("fill", "none")
    .attr("stroke", "white");

  // Create or update the area
  svgStopwatch.selectAll(".lap-area")
    .data([lapTimes])
    .join("path")
    .attr("class", "lap-area")
    .attr("d", areaGenerator)
    .attr("fill", "steelblue")
    .attr("opacity", 0.3);
}

$(document).on('click', '#btn-stopwatch-start-stop', function() {
  console.log("Stopwatch: Start/stop button clicked")
  if ($(this).hasClass('btn-start') || $(this).hasClass('btn-continue')) {
    console.log('Stopwatch: start');
    $(this).removeClass('btn-continue btn-start').addClass('btn-stop');
    $('#btn-stopwatch-lap-reset').removeClass('btn-reset').addClass('btn-lap');
    startStopwatch();
  } else {
    console.log('stopwatch stop');
    $(this).addClass('btn-continue').removeClass('btn-stop');
    $('#btn-stopwatch-lap-reset').removeClass('btn-lap').addClass('btn-reset');
    stopStopwatch();
  }
});

$(document).on('click', '#btn-stopwatch-lap-reset', function() {
  if ($(this).hasClass('btn-reset')) {
    confirm('Are you sure you want to reset the stopwatch?');
    console.log('stopwatch reset');
    resetStopwatch();     
  } else {
    console.log('stopwatch lap');
    addLap();
    
  }
});

$(document).on('change', '#reverse-order', function() {
    var list = $('#ul-stopwatch-laps');
    var listItems = list.children('li');
    list.append(listItems.get().reverse());
});

let isSpeechRecognitionActive = false;
let recognition;

function handleLapButtonClick() {
  console.log('Lap command recognized!');
  document.getElementById('btn-stopwatch-lap-reset').click();
}

function startSpeechRecognition() {
  recognition = new webkitSpeechRecognition();
  recognition.continuous = true;

  recognition.onresult = function(event) {
    for (var i = event.resultIndex; i < event.results.length; i++) {
      if (event.results[i].isFinal) {
        const transcript = event.results[i][0].transcript.toLowerCase();
        document.getElementById('recognized-text').textContent = 'Recognized: ' + transcript;

        if (transcript.includes('next')) {
          handleLapButtonClick();
        }
      }
    }
  };

  recognition.onerror = function(event) {
    console.error('Speech recognition error:', event.error);
  };

  recognition.start();
  isSpeechRecognitionActive = true;
  document.getElementById('btn-speech-recognition').textContent = 'Stop Speech Recognition';

  console.log('Speech recognition started.');
}

function stopSpeechRecognition() {
  if (recognition) {
    recognition.stop();
    isSpeechRecognitionActive = false;
    document.getElementById('btn-speech-recognition').textContent = 'Start Speech Recognition';
    document.getElementById('recognized-text').textContent = '';
    console.log('Speech recognition stopped.');
  }
}

function toggleSpeechRecognition() {
  if (isSpeechRecognitionActive) {
    stopSpeechRecognition();
  } else {
    startSpeechRecognition();
  }
}