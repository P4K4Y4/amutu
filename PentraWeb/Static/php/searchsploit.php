<?php
header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get search term and filters from POST data
    $searchTerm = $_POST['search_term'] ?? '';
    $platform = $_POST['platform'] ?? 'all';
    $type = $_POST['type'] ?? 'all';
    $dateRange = $_POST['date_range'] ?? '';
    $author = $_POST['author'] ?? '';

    // Validate search term
    if (empty(trim($searchTerm))) {
        echo json_encode(["status" => "error", "message" => "Search term cannot be empty."]);
        exit;
    }

    // Construct the searchsploit command
    $command = "searchsploit --json " . escapeshellarg($searchTerm);
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
        $matchesPlatform = ($platform === 'all' || stripos($exploit['Platform'] ?? '', $platform) !== false);
        $matchesType = ($type === 'all' || stripos($exploit['Type'] ?? '', $type) !== false);
        $matchesAuthor = (empty($author) || stripos($exploit['Author'] ?? '', $author) !== false);

        if ($matchesPlatform && $matchesType && $matchesAuthor) {
            $results[] = [
                "id" => $exploit['ID'] ?? 'N/A',
                "title" => $exploit['Title'] ?? 'N/A',
                "platform" => $exploit['Platform'] ?? 'N/A',
                "type" => $exploit['Type'] ?? 'N/A',
                "path" => $exploit['Path'] ?? 'N/A'
            ];
        }
    }

    // Return the results as JSON
    echo json_encode(["status" => "success", "results" => $results]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
