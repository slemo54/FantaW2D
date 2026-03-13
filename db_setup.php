<?php
require_once 'includes/config.php';
require_once 'includes/config_db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require admin
require_admin();

// Include common header
include 'includes/header.php';

// Function to read and execute SQL from file
function execute_sql_file($file_path) {
    $conn = db_connect();
    
    if (!$conn) {
        return "Failed to connect to database";
    }
    
    $sql = file_get_contents($file_path);
    if (!$sql) {
        return "Failed to read SQL file: $file_path";
    }
    
    // Split SQL by semicolon
    $queries = explode(';', $sql);
    $results = [];
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) {
            continue;
        }
        
        if ($conn->query($query)) {
            $results[] = "Success: $query";
        } else {
            $results[] = "Error: " . $conn->error . " in query: $query";
        }
    }
    
    $conn->close();
    
    return $results;
}

// Execute the SQL
$results = execute_sql_file('db_schema.sql');
?>

<div class="container">
    <div class="card">
        <h2>Database Setup Results</h2>
        
        <?php if (is_array($results)): ?>
            <div style="max-height: 400px; overflow-y: auto; margin-bottom: 20px; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd;">
                <pre><?php echo implode("\n", $results); ?></pre>
            </div>
            <p>Database setup completed. Please check the results above for any errors.</p>
        <?php else: ?>
            <div class="error-message">
                <?php echo $results; ?>
            </div>
        <?php endif; ?>
        
        <a href="admin.php" class="btn btn-primary">Back to Admin</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>