<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["message"]) || empty(trim($data["message"]))) {
        echo json_encode(["response" => "Invalid input"]);
        exit;
    }

    $user_input = escapeshellarg($data["message"]);

    // Run tgpt command and capture output
    $output = shell_exec("tgpt $user_input 2>&1");

    if (!$output) {
        $output = "Error: Failed to process request.";
    }

    echo json_encode(["response" => trim($output)]);
    exit;
}
?>
