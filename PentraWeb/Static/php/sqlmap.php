<?php
// Security Note: Add authentication and IP whitelisting in production
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLMap Results</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .back-link { display: inline-block; margin-bottom: 1.5rem; color: #4299e1; text-decoration: none; }
        pre { 
            white-space: pre-wrap; word-wrap: break-word; padding: 1rem; 
            background: #f8f9fa; border-radius: 4px; border: 1px solid #e2e8f0;
        }
        .result-section { margin-bottom: 2rem; }
        .result-title { color: #2d3748; margin-bottom: 0.5rem; }
        .vulnerability { color: #c53030; font-weight: bold; }
        .success { color: #48bb78; }
        .info { color: #4299e1; }
        .loading { 
            display: none; margin: 1rem 0; padding: 1rem; 
            background: #ebf8ff; border-radius: 4px;
        }
        .copy-btn {
            float: right; padding: 0.5rem 1rem; background: #4299e1; 
            color: white; border: none; border-radius: 4px; cursor: pointer;
        }
        .alert {
            padding: 1rem; border-radius: 4px; margin-bottom: 1rem;
        }
        .alert-success { background: #f0fff4; border: 1px solid #c6f6d5; }
        .alert-danger { background: #fff5f5; border: 1px solid #fed7d7; }
    </style>
    <script>
        function copyToClipboard() {
            const el = document.createElement('textarea');
            el.value = document.querySelector('pre').innerText;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            alert('Results copied to clipboard!');
        }
    </script>
</head>
<body>
    <div class="container">
        <a href="../../templates/sqlmap.html" class="back-link">&larr; Back to Scanner</a>
        <button onclick="copyToClipboard()" class="copy-btn">Copy Results</button>
        
        <div class="result-section">
            <h2 class="result-title">Scan Results</h2>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                putenv('HOME=/tmp');
                
                // Get inputs
                $url = escapeshellarg($_POST['url']);
                $options = $_POST['options'];
                
                // Sanitize options
                $allowed_options = ['--risk', '--level', '--dbms', '--technique', '--threads'];
                $sanitized_options = '';
                
                if (!empty($options)) {
                    $args = preg_split('/\s+/', trim($options));
                    foreach ($args as $arg) {
                        if (strpos($arg, '--') === 0) {
                            $opt = explode('=', $arg)[0];
                            if (in_array($opt, $allowed_options)) {
                                $sanitized_options .= escapeshellarg($arg) . ' ';
                            }
                        }
                    }
                }

                // Build command
                $command = "sqlmap -u $url $sanitized_options --batch 2>&1";
                
                // Execute and format output
                echo '<div class="alert alert-info">';
                echo "<strong>Executing:</strong> <code>{$command}</code>";
                echo '</div>';
                
                echo '<div class="alert alert-success">';
                echo '<pre>';
                
                $output = [];
                $has_vulnerability = false;
                exec($command, $output, $return_var);
                
                foreach ($output as $line) {
                    $formatted_line = htmlspecialchars($line);
                    
                    // Highlight important information
                    if (strpos($line, '[CRITICAL]') !== false) {
                        echo "<span class='vulnerability'>$formatted_line</span>\n";
                        $has_vulnerability = true;
                    } elseif (strpos($line, '[INFO]') !== false) {
                        echo "<span class='info'>$formatted_line</span>\n";
                    } else {
                        echo "$formatted_line\n";
                    }
                }
                
                echo '</pre></div>';
                
                // Show summary
                echo '<div class="alert ' . ($has_vulnerability ? 'alert-danger' : 'alert-success') . '">';
                echo '<h3>Scan Summary:</h3>';
                if ($has_vulnerability) {
                    echo '<p class="vulnerability">⚠️ Potential vulnerabilities detected!</p>';
                } else {
                    echo '<p class="success">✅ No obvious vulnerabilities detected</p>';
                }
                echo "<p>Exit code: $return_var</p>";
                echo '</div>';
                
            } else {
                echo '<div class="alert alert-danger">Invalid request method!</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
