<?php
header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get search term and filters from POST data
    $searchTerm = escapeshellarg($_POST['search_term'] ?? '');
    $platform = escapeshellarg($_POST['platform'] ?? 'all');
    $type = escapeshellarg($_POST['type'] ?? 'all');
    $dateRange = escapeshellarg($_POST['date_range'] ?? '');
    $author = escapeshellarg($_POST['author'] ?? '');

    // Validate search term
    if (empty(trim($_POST['search_term']))) {
        echo json_encode(["status" => "error", "message" => "Search term cannot be empty."]);
        exit;
    }

    // Construct the searchsploit command
    $command = "searchsploit --json $searchTerm";
    $output = shell_exec($command);

    if ($output === null) {
        echo json_encode(["status" => "error", "message" => "Failed to execute searchsploit."]);
        exit;
    }

    // Decode JSON output from searchsploit
    $data = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['RESULTS_EXPLOIT'])) {
        echo json_encode(["status" => "error", "message" => "Invalid response from searchsploit."]);
        exit;
    }

    // Filter results based on additional criteria
    $results = [];
    foreach ($data['RESULTS_EXPLOIT'] as $exploit) {
        if (($platform === "'all'" || stripos($exploit['platform'], trim($_POST['platform'])) !== false) &&
            ($type === "'all'" || stripos($exploit['type'], trim($_POST['type'])) !== false) &&
            ($author === "''" || stripos($exploit['author'] ?? '', trim($_POST['author'])) !== false)) {
                
            $results[] = [
                "id" => $exploit['id'] ?? 'N/A',
                "title" => $exploit['title'] ?? 'N/A',
                "platform" => $exploit['platform'] ?? 'N/A',
                "type" => $exploit['type'] ?? 'N/A',
                "path" => $exploit['path'] ?? 'N/A'
            ];
        }
    }

    // Return the results as JSON
    echo json_encode(["status" => "success", "results" => $results]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
