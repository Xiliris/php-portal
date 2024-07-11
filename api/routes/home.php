<?php
// routes/home.php
function getHome() {
    // Example JSON response
    $homeData = array(
        'title' => 'Home Page',
        'content' => 'This is the home page content.'
    );

    header('Content-Type: application/json');
    echo json_encode($homeData);
}
