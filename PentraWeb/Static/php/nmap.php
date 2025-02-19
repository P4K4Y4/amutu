<?php
header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = $_POST['target'];
    $scan_type = $_POST['scan_type'];

    // Validate target input (basic example)
    if (!preg_match('/^[a-zA-Z0-9\.\-]+$/', $target)) {
        echo json_encode(["status" => "error", "message" => "Invalid target input."]);
        exit;
    }

    // Build Nmap command based on scan type
    $command = "nmap";
    if ($scan_type === "quick") {
        $command .= " -T4 -F";
    } elseif ($scan_type === "full") {
        $command .= " -A";
    } elseif ($scan_type === "custom") {
        // Add custom options
        $command .= " -p 1-1000 -O -sV";
    }
    $command .= " $target";

    // Run Nmap command
    exec($command, $output, $return_var);

    // Check if Nmap command failed
    if ($return_var !== 0) {
        echo json_encode(["status" => "error", "message" => "Nmap command failed. Ensure Nmap is installed and accessible."]);
        exit;
    }

    // Parse Nmap output to extract results
    $scan_results = [];
    foreach ($output as $line) {
        if (preg_match('/^(\d+\/\w+)\s+(\w+)\s+([\w\-]+)/', $line, $matches)) {
            $scan_results[] = [
                "port" => $matches[1],
                "state" => $matches[2],
                "service" => $matches[3]
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