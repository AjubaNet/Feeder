<?php
// Configuration
$remoteURL = "https://example.com"; // Replace with your remote server's base URL
$cacheDir = __DIR__ . "/cache";    // Directory to cache files locally (relative to this script)

// Ensure the cache directory exists
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Get the requested resource path
$requestURI = $_SERVER['REQUEST_URI']; // e.g., /path/to/resource
$resourcePath = trim($requestURI, "/"); // Remove leading slash for relative path
$localPath = $cacheDir . DIRECTORY_SEPARATOR . $resourcePath; // Full path in cache

// Ensure nested directories are created for cached files
$localDir = dirname($localPath);
if (!file_exists($localDir)) {
    mkdir($localDir, 0755, true);
}

if (file_exists($localPath)) {
    // Serve the cached file
    serveFile($localPath);
} else {
    // Fetch the file from the remote server
    $remoteFileURL = $remoteURL . "/" . $resourcePath;

    // Fetch the resource
    $resourceContent = fetchRemoteResource($remoteFileURL);

    if ($resourceContent !== false) {
        // Save to local cache
        file_put_contents($localPath, $resourceContent);

        // Serve the file
        serveContent($resourceContent, $resourcePath);
    } else {
        // Resource could not be fetched; return 404
        header("HTTP/1.1 404 Not Found");
        echo "Error: Resource not found.";
    }
}

// Function to fetch a resource from a remote server
function fetchRemoteResource($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Timeout after 15 seconds
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200 ? $response : false;
}

// Function to serve a file
function serveFile($filePath) {
    $mimeType = mime_content_type($filePath);
    header("Content-Type: $mimeType");
    readfile($filePath);
    exit;
}

// Function to serve content directly
function serveContent($content, $resourcePath) {
    $mimeType = mime_content_type($resourcePath); // Infer MIME type from file extension
    header("Content-Type: $mimeType");
    echo $content;
    exit;
}
?>
