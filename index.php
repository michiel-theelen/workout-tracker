        <?php 
        // start session
        session_start();
        // config database
        require_once 'config.php';
        // set csrf token
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } 
        
        // include header
        include('header.php'); 
        ?>
        
        <div class="container" id="container-index">

            <nav class="side-menu hide" id="side-menu">           
                <button class="btn-nav btn-tracker" onclick="loadPage('content_tracker.php')">Tracker</button>
                <button class="btn-nav btn-chart" onclick="loadPage('content_stopwatch.php')">Stopwatch</button>
                <button class="btn-nav btn-data" onclick="loadPage('content_settings.php')">Manage Data</button>
                <button class="btn-nav btn-user" onclick="loadPage('content_settings.php')">User Settings</button>
                <button class="btn-nav btn-logout" onclick="logout()">Logout</button>
            </nav>

            <main>
                <?php include('content_index.php'); ?>
            </main>
        </div>
    </body>
</html>
