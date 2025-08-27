<?php
// Test PostgreSQL connection
echo "Testing PostgreSQL support...\n";

// Check if PDO PostgreSQL extension is loaded
if (extension_loaded('pdo_pgsql')) {
    echo "✅ PDO PostgreSQL extension is loaded\n";
} else {
    echo "❌ PDO PostgreSQL extension is NOT loaded\n";
    echo "Please enable it in php.ini:\n";
    echo "1. Open D:\\software\\xampp\\php\\php.ini\n";
    echo "2. Find and uncomment: extension=pdo_pgsql\n";
    echo "3. Restart Apache in XAMPP\n";
    exit(1);
}

// Test connection to local PostgreSQL
$dsn = 'pgsql:host=127.0.0.1;port=5432;dbname=mkt_calculator;';
$username = 'mkt_user';
$password = 'mkt_password';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30, // 30 second timeout
    ]);

    echo "✅ Successfully connected to PostgreSQL!\n";

    // Test a simple query
    $stmt = $pdo->query('SELECT version()');
    $version = $stmt->fetchColumn();
    echo "Database version: $version\n";

    // Check if our tables exist
    $stmt = $pdo->prepare("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Existing tables: " . (empty($tables) ? "None" : implode(', ', $tables)) . "\n";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 PostgreSQL connection test completed successfully!\n";
