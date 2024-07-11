<?php
function createPost() {
    // Get JSON data from POST request body
    $json_data = file_get_contents('php://input');
    $post_data = json_decode($json_data, true);

    // Validate and process the received data
    if (isset($post_data['title']) && isset($post_data['content'])) {
        // Example: Save the post data (database operation, etc.)
        // For demonstration, let's assume we're storing in a variable
        $new_post = array(
            'id' => 1,  // Example ID
            'title' => $post_data['title'],
            'content' => $post_data['content']
        );

        // Respond with success message and newly created post data
        http_response_code(201);  // HTTP status code for Created
        echo json_encode(array('message' => 'Post created successfully', 'post' => $new_post));
    } else {
        // Invalid or incomplete data
        http_response_code(400);  // HTTP status code for Bad Request
        echo json_encode(array('error' => 'Invalid data'));
    }
}
