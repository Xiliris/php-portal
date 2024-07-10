<?php
// routes/api/user.php
function getUserData() {
    // Example JSON response
    $userData = array(
        'name' => 'John Doe',
        'email' => 'john.doe@example.com'
    );

    header('Content-Type: application/json');
    echo json_encode($userData);
}
