<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session to access username
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if image was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] > 0) {
        echo json_encode(['error' => 'No image uploaded or upload error']);
        exit;
    }

    // Get form data
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : 'Unknown';
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : 'Unknown';
    $area = isset($_POST['area']) ? $_POST['area'] : 'Unknown';
    $city = isset($_POST['city']) ? $_POST['city'] : 'Unknown';
    $username = isset($_POST['username']) ? $_POST['username'] : $_SESSION['username'] ?? 'Unknown';
    
    // Initialize cURL session
    $url = 'http://localhost:5000/process_image';
    $ch = curl_init($url);
    
    // Create post fields array including the file
    $cfile = new CURLFile(
        $_FILES['image']['tmp_name'], 
        $_FILES['image']['type'], 
        $_FILES['image']['name']
    );
    
    $post_data = [
        'image' => $cfile,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'area' => $area,
        'city' => $city,
        'username' => $username
    ];
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set timeout to 30 seconds
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo json_encode([
            'error' => 'Connection error: ' . curl_error($ch),
            'curl_error_code' => curl_errno($ch)
        ]);
        curl_close($ch);
        exit;
    }
    
    // Get HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Set content type to JSON
    header('Content-Type: application/json');
    
    if ($http_code >= 200 && $http_code < 300) {
        // Forward the response from Flask
        echo $response;
    } else {
        // Log the error response for debugging
        error_log("Flask API error response: " . $response);
        echo json_encode([
            'error' => 'Server returned status code: ' . $http_code,
            'details' => json_decode($response, true) ?? $response
        ]);
    }
    exit;
} else {
    // Handle non-POST requests
    echo json_encode(['error' => 'Invalid request method']);
}
?>