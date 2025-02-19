<?php
header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $target = filter_input(INPUT_POST, 'target', FILTER_SANITIZE_STRING);
    $scan_type = filter_input(INPUT_POST, 'scan_type', FILTER_SANITIZE_STRING);

    // Validate target input (basic example for IP/hostname)
    if (!preg_match('/^[a-zA-Z0-9\.\-]+$/', $target)) {
        echo json_encode(["status" => "error", "message" => "Invalid target input."]);
        exit;
    }

    // Validate scan type
    $allowed_scan_types = ["quick", "full", "custom"];
    if (!in_array($scan_type, $allowed_scan_types)) {
        echo json_encode(["status" => "error", "message" => "Invalid scan type."]);
        exit;
    }

    // Build Nmap command
    $command = ["nmap"];
    switch ($scan_type) {
        case "quick":
            $command[] = "-T4";
            $command[] = "-F";
            break;
        case "full":
            $command[] = "-A";
            break;
        case "custom":
            $command[] = "-p";
            $command[] = "1-1000";
            $command[] = "-O";
            $command[] = "-sV";
            break;
    }
    $command[] = escapeshellarg($target); // Sanitize target input

    // Execute Nmap command
    exec(implode(" ", $command), $output, $return_var);

    // Check if Nmap command failed
    if ($return_var !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Nmap command failed. Ensure Nmap is installed and accessible.",
            "output" => $output // Include Nmap output for debugging
        ]);
        exit;
    }

    // Parse Nmap output to extract results
    $scan_results = [];
    $os_detection = "";
    foreach ($output as $line) {
        // Match open ports, state, service, and version details
        if (preg_match('/^(\d+\/\w+)\s+(\w+)\s+([\w\-\s]+)\s*(.*)$/', $line, $matches)) {
            $scan_results[] = [
                "port" => $matches[1],
                "state" => $matches[2],
                "service" => trim($matches[3]),
                "version" => trim($matches[4]) ?: "N/A"
            ];
        }

        // Capture OS detection details
        if (strpos($line, "OS details:") !== false) {
            $os_detection = trim(str_replace("OS details:", "", $line));
        }
    }

    // Generate HTML table
    if (!empty($scan_results)) {
        $table = '<h3>Scan Results</h3><table border="1" cellpadding="5">';
        $table .= '<thead><tr><th>Port</th><th>State</th><th>Service</th><th>Version</th></tr></thead><tbody>';
        foreach ($scan_results as $result) {
            $table .= "<tr>
                        <td>{$result['port']}</td>
                        <td>{$result['state']}</td>
                        <td>{$result['service']}</td>
                        <td>{$result['version']}</td>
                      </tr>";
        }
        $table .= '</tbody></table>';

        // Include OS detection in results
        if (!empty($os_detection)) {
            $table .= "<h3>OS Detection</h3><p>{$os_detection}</p>";
        }

        echo json_encode(["status" => "success", "html" => $table]);
    } else {
        echo json_encode(["status" => "error", "message" => "No open ports found."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
