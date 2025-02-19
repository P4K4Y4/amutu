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
    foreach ($output as $line) {
        if (preg_match('/^(\d+\/\w+)\s+(\w+)\s+([\w\-\s]+)/', $line, $matches)) {
            $scan_results[] = [
                "port" => $matches[1],
                "state" => $matches[2],
                "service" => trim($matches[3])
            ];
        }
    }

    // Generate HTML table
    if (!empty($scan_results)) {
        $table = '<h3>Scan Results</h3><table>';
        $table .= '<thead><tr><th>Port</th><th>State</th><th>Service</th></tr></thead><tbody>';
        foreach ($scan_results as $result) {
            $table .= "<tr>
                        <td>{$result['port']}</td>
                        <td>{$result['state']}</td>
                        <td>{$result['service']}</td>
                      </tr>";
        }
        $table .= '</tbody></table>';
        echo json_encode(["status" => "success", "html" => $table]);
    } else {
        echo json_encode(["status" => "error", "message" => "No open ports found."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
