<?php
function getUploadDir() {
    return $_SERVER['DOCUMENT_ROOT'] . '/d/dashboard/uploads/products/';
}

function getWebImagePath($db_path) {
    // Handle external URLs
    if (strpos($db_path, 'http') === 0) {
        return $db_path;
    }
    
    // Handle full server paths (from sellerpanel)
    if (strpos($db_path, '/d/dashboard/') === 0) {
        return $db_path;
    }
    
    // Handle relative paths (from adminpanel)
    return '/d/dashboard/uploads/products/' . ltrim($db_path, 'uploads/products/');
}

function saveUploadedImage($file) {
    $uploadDir = getUploadDir();
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Return relative path for database storage
        return 'uploads/products/' . $filename;
    }
    
    return false;
}
?>