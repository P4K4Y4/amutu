<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLMap Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        pre { background: #f4f4f4; padding: 15px; overflow-x: auto; }
        a { color: #4CAF50; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>SQLMap Results</h1>
        <a href="index.html">&larr; Back to Form</a>
        <pre>
<?php
// Security Note: This script is for educational purposes only. 
// Ensure proper authentication and input validation in production environments.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set environment variables for www-data
    putenv('HOME=/tmp');

    // Get user inputs
    $url = escapeshellarg($_POST['url']);
    $options = $_POST['options'];

    // Validate and sanitize additional options
    $allowed_options = ['--risk', '--level', '--dbms', '--technique', '--threads'];
    $sanitized_options = '';

    if (!empty($options)) {
        $args = explode(' ', $options);
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $opt = explode('=', $arg)[0];
                if (in_array($opt, $allowed_options)) {
                    $sanitized_options .= escapeshellarg($arg) . ' ';
                }
            }
        }
    }

    // Build the sqlmap command
    $command = "sqlmap -u $url $sanitized_options --batch 2>&1";
    
    // Execute the command
    echo "<strong>Command Executed:</strong>\n$command\n\n";
    echo "<strong>Results:</strong>\n";
    passthru($command, $return_var);

    if ($return_var !== 0) {
        echo "\n\nError: SQLMap exited with code $return_var";
    }
} else {
    echo "Invalid request method!";
}
?>
        </pre>
    </div>
</body>
</html>
