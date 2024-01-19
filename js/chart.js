let data = [];
let filteredData = []
let colorScale = [];
let verticalLine;
let container;
let margin;
let svg;
let chartGroup;
let legend;
let xScale;
let yScale;
let chartWidth;
let chartHeight;

// add handle
let handle;
const handleWidth = 40;
const handleHeight = 30;
const handleYPosition = -40;
let handleXPosition = 0;
let oldSelectedDate = 0;
const handleCornerRadius = 3;
const handleColor = 'red';

initChart();

async function initChart() {

  data = await loadData();

  margin = { top: 60, right: 0, bottom: 50, left: 0 };
  container = d3.select("#chart");
  chartWidth = container.node().getBoundingClientRect().width - margin.left - margin.right;
  chartHeight = container.node().getBoundingClientRect().height - margin.top - margin.bottom;

  // create original container
  svg = container.append("svg")
    .attr("width", "100%")
    .attr("height", "100%")
    .attr("font-family", "'Montserrat', sans-serif")

  chartGroup = svg.append("g")
    .attr("transform", `translate(${margin.left},${margin.top})`);

  // After setting up your svg and appending your group...
  let background = chartGroup.append("rect")
      .attr("class", "chart-background")
      .attr("width", "100%")
      .attr("height", chartHeight)
      .style("pointer-events", "all"); // This makes the rect respond to mouse events

  // Append a vertical line to the chart
  verticalLine = chartGroup.append('line')
    .attr('stroke', 'black')
    .attr('y1', 0)
    .attr('y2', chartHeight)

  // append legend to body
  legend = d3.select('.chart-legend').append('div')
    .attr('class', 'legend');

  xScale = d3.scaleTime().range([0, chartWidth]);
  yScale = d3.scaleLinear().range([chartHeight, 0]);

  xAxis = d3.axisBottom(xScale)
    .tickPadding(10)
    .tickSizeInner(-10)
    .ticks(d3.timeDay.every(1))
    .tickFormat('') // suppress tick text

  yAxis = d3.axisLeft(yScale)
    .tickPadding(10)
    .tickSizeInner(-10);
    // .tickPadding(100);

  // Append x-axis to chart
  chartGroup.append("g")
    .attr("class", "chart-xaxis")
    .attr("transform", "translate(0," + chartHeight + ")")
    .call(xAxis);

  // Append y-axis to chart
  chartGroup.append("g")
    .attr("class", "chart-yaxis")
    .attr("transform", "translate(0,0)")
    .call(yAxis);

  // Update tick text on y-axis
  chartGroup.selectAll(".chart-yaxis .tick text")
    .attr("x", 30)
    .attr("dy", 5)
    .attr("text-anchor", "end");

  // Append handle to chart
  handle = chartGroup.append('rect')
    .attr("class", "handle")
    .attr('x', handleXPosition)
    .attr('y', chartHeight - handleHeight - handleYPosition)
    .attr('width', handleWidth)
    .attr('height', handleHeight)
    .attr('rx', handleCornerRadius) // Set the horizontal corner radius
    .attr('ry', handleCornerRadius) // Set the vertical corner radius
    .attr('fill', handleColor)
    .call(d3.drag()
      .on("start", dragstarted)
      .on("drag", throttle(dragged, 20))
      .on("end", dragended));

  const exerciseNames = [...new Set(data.map((d) => d.exercise_name))];
  colorScale = d3.scaleOrdinal(d3.schemeCategory10).domain(exerciseNames);

  // Add data to chart
  updateChart()
}

async function loadData() {

  // fetch database
  const response = await fetch('load_data2.php');

  if (response.ok) {
    // store data in arrays
    data = await response.json();
    filteredData = data;
    // Success message
    console.log("Success fetching data!");
    return data;
  } else {
    // Error message
    console.error(`Error fetching data: ${response.status} ${response.statusText}`);
    return [];
  }
}

function updateChart() {

  onResize();

  const selectExerciseData = chartSelectExercise();
  console.log(selectExerciseData);
  filteredData = chartSelectDataByMetric(selectExerciseData);
  chartSelectDateRange();
  chartSelectWeightRange();

  // Set verticalLine to the latest date in the data
  const latestDate = d3.max(filteredData, d => new Date(d.date));
  handleXPosition = xScale(latestDate); 

  // Get x-position of handle
  let x = handleXPosition;
  // Set boundaries
  if (x < handleWidth / 2) x = handleWidth / 2;
  if (x > chartWidth - handleWidth / 2) x = chartWidth - handleWidth / 2;

  verticalLine
    .transition(300)
    .attr("x1", x)
    .attr("x2", x)
  // Set handle to the latest date in the data
  handle
    .transition(300)  
    .attr('x', x - handleWidth / 2);

  updatelegend(x);

  // Update existing circles with new data
  const circles = chartGroup.selectAll("circle")
    .data(filteredData, d => d.id)
    .join(
      // Append new circles with new data
      enter => enter.append("circle")
        .attr("r", 5)
        .attr("cx", d => xScale(new Date(d.date)))
        .attr("cy", d => yScale(d.weight))
        .attr("fill", (d) => colorScale(d.exercise_name))
        .attr("stroke", "black")
        .attr("opacity", 0)
        .transition()
          .delay(300)
          .duration(300)
          .attr("opacity", 1),

      // Update existing circles with new data
      update => update.transition()
        .duration(300)
        .attr("cx", d => xScale(new Date(d.date)))
        .attr("cy", d => yScale(d.weight)),
        
      // Remove old circles that are not in the new data
      exit => exit.transition()
        .duration(100)
        .attr("opacity", 0)
        .remove(),
    )
  
  // Create bands in the background of the chart
  const bandSize = 10;
  const maxWeight = Math.max(...filteredData.map(d => Number(d.weight)));
  const numBands = Math.ceil(maxWeight / bandSize);
  
  // Make sure to remove any previous bands before appending new ones
chartGroup.selectAll("rect.band").remove();

let bands = chartGroup.selectAll("rect.band")
    .data(d3.range(0, numBands)); // replace 10 with the desired band size

bands
    .enter()
    .append("rect")
    .attr("class", "band") // this helps differentiate bands from other rectangles
    .attr("x", 0)
    // d is the index, so we add 1 to make it start from 1 instead of 0, then multiply by bandSize
    .attr("width", chartWidth)
     // yScale is inverted, so yScale(0) will be the bottom of the chart and yScale(bandSize) will be the top of the band
    .attr("fill", d => d % 2 === 0 ? "rgba0,0,0,0.1" : "rgba(0,0,0,0.2")
    .attr("y", d => yScale((d + 1) * bandSize))
    .attr("height", yScale(0) - yScale(bandSize))
    .attr("opacity", 0)
    .transition() // Transition on enter
    .duration(300)
    .attr("opacity", 1);

bands
    .transition() // Transition on update
    .duration(1000)
    .attr("y", d => yScale((d + 1) * bandSize))
    .attr("height", yScale(0) - yScale(bandSize));

bands
    .exit()
    .transition() // Transition on exit
    .duration(1000)
    .attr("y", d => yScale((d + 1) * bandSize))
    .attr("height", yScale(0) - yScale(bandSize))
    .remove();

console.log('Chart Updated!');
}

function chartSelectDateRange() {
  const selectedRange = document.getElementById('dateRangeSelector').value;
  // Convert selected range to dates
  const today = new Date();
  today.setHours(0,0,0,0);

  const endDate = d3.max(filteredData, d => new Date(d.date));
  let startDate;
  switch (selectedRange) {
    case "1w":
      startDate = new Date();
      startDate.setDate(today.getDate() - 7);
      break;
    case "1m":
      startDate = new Date();
      startDate.setMonth(today.getMonth() - 1);
      break;
    case "1y":
      startDate = new Date();
      startDate.setFullYear(today.getFullYear() - 1);
      break;
    default:
      startDate = d3.min(filteredData, d => new Date(d.date));
  }

  // Calculate the range of dates
  const dateRange = endDate - startDate;

  // Calculate the margin as a proportion of the date range
  const margin = dateRange * 0.075; //
  // Add the margin to the start and end dates
  const domainStart = new Date(startDate.getTime() - margin);
  const domainEnd = new Date(endDate.getTime() + margin);

  // Update scale
  xScale.domain([domainStart, domainEnd]);

  // Update circles
  chartGroup.selectAll("circle")
    .transition()
    .duration(300)
    .attr("cx", d => xScale(new Date(d.date)));

  // Update x-axis
  chartGroup.select(".chart-xaxis")
    .transition()
    .duration(300)
    .call(xAxis.scale(xScale)); // Update the scale of the x-axis

  // Set verticalLine to the latest date in the data
  const latestDate = d3.max(filteredData, d => new Date(d.date));
  handleXPosition = xScale(latestDate); 
  verticalLine
    .transition(300)
    .attr("x1", handleXPosition)
    .attr("x2", handleXPosition)
  // Set handle to the latest date in the data
  handle
    .transition(300)  
    .attr('x', handleXPosition - handleWidth / 2);

  updatelegend(handleXPosition);
}

function chartSelectWeightRange() {
  // Update y-axis
  const maxWeight = Math.max(...filteredData.map(d => Number(d.weight)));
  const yDomain = [0, maxWeight];

  yScale.domain(yDomain);

  chartGroup.select(".chart-yaxis")
    .transition()
    .duration(300)
    .call(yAxis.scale(yScale));
}

function chartSelectExercise() {
  const selectedExercise = document.getElementById('exerciseSelector').value;
  // Filter data based on selected exercise
  const selectExerciseData = data.filter(d => selectedExercise === 'all' || d.exercise_id === selectedExercise);
  // Update circles and y-axis
  yScale.domain([0, d3.max(filteredData, d => d.weight)]);
  return selectExerciseData;
}

function chartSelectDataByMetric(exerciseData) {
  const selectedData = document.getElementById('dataSelector').value;
  let weightCalculator;

  switch (selectedData) {
    case "max":
      // Function to get the max weight and corresponding ID for each group
      weightCalculator = v => {
        const maxWeight = d3.max(v, d => d.weight);
        const id = v.find(d => d.weight === maxWeight).id;
        return {weight: maxWeight, id};
      };
      break;

    case "total":
      // Function to get the total weight and the ID of the first data point for each group
      weightCalculator = v => {
        const totalWeight = d3.sum(v, d => d.weight);
        const maxWeight = d3.max(v, d => d.weight);
        const id = v.find(d => d.weight === maxWeight).id;
        return {weight: totalWeight, id};
      };
      break;

    default:
      // If "all" is selected, just use the original exercise data
      return exerciseData;
  }

  // Group by exercise and date, then calculate the weight using the weightCalculator function
  const processedData = d3.rollup(
    exerciseData, 
    weightCalculator, 
    d => d.exercise_name, 
    d => new Date(d.date).toDateString()
  );

  // Convert Map to array of objects for use with D3
  return Array.from(processedData, ([exercise_name, dates]) => 
    Array.from(dates, ([date, weight_id]) => ({exercise_name, date: new Date(date), weight: weight_id.weight, id: weight_id.id}))
  ).flat();
}

function chartAddDataPoint() {
  // Get the form values
  const newExerciseName = document.getElementById('name').value;
  const newDate = document.getElementById('date').value;
  const newReps = document.getElementById('reps').value;
  const newWeight = document.getElementById('weight').value;

  // Create a new id
  const newId = Date.now();

  // Add the new data to the data array
  data.push({
    id: newId,
    exercise_name: newExerciseName,
    date: newDate,
    reps: +newReps, // convert to number using unary plus operator
    weight: +newWeight, // convert to number using unary plus operator
  });

  // Update x-axis domain
  const minDate = d3.min(data, d => new Date(d.date));
  const maxDate = d3.max(data, d => new Date(d.date));
  xScale.domain([minDate, maxDate]);

  // Update x-axis
  chartGroup.select(".chart-xaxis")
    .transition()
    .duration(1000)
    .call(xAxis.scale(xScale));

  // Call the updateChart function to update the chart with the new data
  updateChart();
}


function dragstarted() {
  d3.select(this).raise().classed("active", true);
}

function dragged(event, d) {
  
  console.log('drag');
  // Get x-position of handle
  let x = event.x;
  // Set boundaries
  if (x < handleWidth / 2) x = handleWidth / 2;
  if (x > chartWidth - handleWidth / 2) x = chartWidth - handleWidth / 2;

  // Update positions
  verticalLine
    .attr("x1", x)
    .attr("x2", x)
    .raise();
  handle
    .attr("x", x - handleWidth / 2)
    .raise();

  // Move the vertical line to the nearest data point
  const xDate = xScale.invert(x);
  let nearestDataPoint = filteredData.reduce((a, b) => {
    let aDistance = Math.abs(new Date(a.date) - xDate);
    let bDistance = Math.abs(new Date(b.date) - xDate);
    return aDistance < bDistance ? a : b;
  });

  // Filter the data for the current date
  newSelectedDate = nearestDataPoint.date;

  if (oldSelectedDate != newSelectedDate) {
    oldSelectedDate = newSelectedDate;
    updatelegend(x);
  }

  
}

function dragended(event, d) {
  
  // Get x-position of handle
  let x = event.x;
  // Set boundaries
  if (x < handleWidth / 2) x = handleWidth / 2;
  if (x > chartWidth - handleWidth / 2) x = chartWidth - handleWidth / 2;

  // Find nearest data point
  var xDate = xScale.invert(x);
  let nearestDataPoint = filteredData.reduce((a, b) => {
    let aDistance = Math.abs(new Date(a.date) - xDate);
    let bDistance = Math.abs(new Date(b.date) - xDate);
    return aDistance < bDistance ? a : b;
  });

  // Move the vertical line to the nearest data point
  const nearestDate = new Date(nearestDataPoint.date);
  x = xScale(nearestDate);
  if (x < handleWidth / 2) x = handleWidth / 2;
  if (x > chartWidth  - handleWidth / 2) x = chartWidth - handleWidth / 2;

  // Set the x-position of the handle
  handle.transition()
    .duration(300)
    .attr('x', x - handleWidth / 2);

  // Update the position of the vertical line
  verticalLine.transition()
    .duration(300)
    .attr('x1', x)
    .attr('x2', x);
}

function updatelegend(x) {

  
  // Move the vertical line to the nearest data point
  const xDate = xScale.invert(x);
  let nearestDataPoint = filteredData.reduce((a, b) => {
    let aDistance = Math.abs(new Date(a.date) - xDate);
    let bDistance = Math.abs(new Date(b.date) - xDate);
    return aDistance < bDistance ? a : b;
  });

  // Filter the data for the current date
  const nearestDate = new Date(nearestDataPoint.date);
  let legendData = filteredData.filter(d => (new Date(d.date)).toDateString() === nearestDate.toDateString());


  // Show legend content of selected date
  let dateForlegend = d3.timeFormat("%B %d, %Y")(new Date(nearestDataPoint.date));
  legend.html(`${dateForlegend}<hr>` + 
    legendData.map(d => `
        <span class="legend-dot" style="background-color: ${colorScale(d.exercise_name)};"></span>
        ${d.exercise_name}: ${d.reps} x ${d.weight}kg
    `).join('<hr>'))
    .transition(300);

  console.log("legend changed to: " + xDate);
}

// throttle function
function throttle(func, limit) {
  let inThrottle;
  return function() {
    const args = arguments;
    const context = this;
    if (!inThrottle) {
      func.apply(context, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  }
}

// Debounce function
function debounce(func, wait) {
  let timeout;

  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };

    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Resize function
function onResize() {
  // Get new dimensions
  let newChartWidth = container.node().getBoundingClientRect().width - margin.left - margin.right;
  let newChartHeight = container.node().getBoundingClientRect().height - margin.top - margin.bottom;

  // Check if dimensions have changed
  if (newChartWidth !== chartWidth || newChartHeight !== chartHeight) {
    // Update dimensions
    chartWidth = newChartWidth;
    chartHeight = newChartHeight;

    // Update scales
    xScale.range([0, chartWidth]);
    yScale.range([chartHeight, 0]);

    // Append x-axis to chart
    chartGroup.select(".chart-xaxis")
    .attr("transform", "translate(0," + chartHeight + ")")
    // .call(xAxis);

    // Append a vertical line to the chart
    verticalLine 
      .attr('y2', chartHeight);

    // Append handle to chart
    handle 
      .attr('y', chartHeight - handleHeight - handleYPosition);
  }
}

// Update chart on resize
let previousInnerWidth = window.innerWidth;
let debouncedUpdateChart = debounce(updateChart, 250);
window.addEventListener('resize', function() {
    let newInnerWidth = window.innerWidth;
    if (newInnerWidth !== previousInnerWidth) {
        debouncedUpdateChart();
        previousInnerWidth = newInnerWidth;
    }
});




function chartFullScreen() {
    let body = document.querySelector("body");
    let container = document.getElementById("chart-container");

    if (!container.classList.contains("fullscreen")) {
        // if not fullscreen, append to body and make fullscreen
        container.remove();
        body.appendChild(container);
        body.classList.add("no-scroll");
        container.classList.add("fullscreen");
        
    } else {
        // if already fullscreen, append back to main and remove fullscreen
        let main = document.querySelector("main");
        container.remove();
        main.appendChild(container);
        body.classList.remove("no-scroll");
        container.classList.remove("fullscreen");
        
    }

    updateChart();
}