<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$servername = "db1034.awardspace.net";
$username = "4669157_educ"; // Replace with your MySQL username
$password = "Ilovemyself"; // Replace with your MySQL password
$dbname = "4669157_educ";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = isset($_GET['username']) ? $_GET['username'] : '';

    if (empty($username)) {
        echo json_encode(['error' => 'Username is required']);
        exit;
    }

    $stmt = $conn->prepare("SELECT reward_points, level FROM rewards WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'reward_points' => (int)$user['reward_points'],
            'level' => (int)$user['level']
        ]);
    } else {
        echo json_encode(['reward_points' => 0, 'level' => 1]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>