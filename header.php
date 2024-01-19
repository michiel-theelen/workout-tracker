<?php
// start sessions
session_start();
// config database
require_once 'config.php';
// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    include('load_data.php');
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Workout Tracker</title>

        <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts@5.4.2/dist/echarts.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
        
        <script src="https://d3js.org/d3.v7.min.js"></script>


        <link rel="preconnect" href="https://fonts.googleapis.com"> 
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;400;600;900&display=swap" rel="stylesheet">

        <link href="style.css" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="ico/favicon.svg">
        <link rel='mask-icon' href='ico/favicon-mask.svg' color='rgb(250,197,70)'>

        <script src="js/scripts.js" defer></script>
        <script src="js/stopwatch.js" defer></script>
        <script src="js/data.js" defer></script>
        <script src="js/chart.js" defer></script>

    </head>

    <body class="prevent-select">

        <!-- header -->
        <header>
            <div id="container-header">
                <!-- icon -->
                <img onclick="loadPage('content_index.php')" src="ico/favicon.svg"/>
                <!-- hamburger button -->
                <h1>Workout Tracker</h1>
                <button class="btn-hamburger prevent-select" id="btn-menu-toggle" onclick="toggleMenu(this)">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </button>
            </div>
        </header>