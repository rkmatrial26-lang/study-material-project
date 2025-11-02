<?php
require_once '../config.php';
require_once 'cloudinary_config.php';

use Cloudinary\Api\Upload\UploadApi;

// **IMPROVED SECURITY CHECK**
// The script will not run at all if an admin is not logged in.
if (!isset($_SESSION['admin_id'])) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// This handles the request from the TinyMCE editor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $temp_file = $_FILES['file']['tmp_name'];
        
        try {
            $uploadApi = new UploadApi();
            // Upload the file to your "study_material" folder in Cloudinary
            $upload_result = $uploadApi->upload($temp_file, ["folder" => "study_material"]);
            $image_url = $upload_result['secure_url'];

            // Respond to TinyMCE with the image URL in the required JSON format
            echo json_encode(['location' => $image_url]);

        } catch (Exception $e) {
            header("HTTP/1.1 500 Server Error");
            echo json_encode(['error' => 'Failed to upload to Cloudinary: ' . $e->getMessage()]);
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['error' => 'No file uploaded or an upload error occurred.']);
    }
} else {
    header("HTTP/1.1 405 Method Not Allowed");
}