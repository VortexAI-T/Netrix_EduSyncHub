<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "fdb1034.awardspace.net";
$username = "4669157_educ"; // Replace with your MySQL username
$password = "Ilovemyself"; // Replace with your MySQL password
$dbname = "4669157_educ";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $username = isset($data['username']) ? $data['username'] : '';
    $reward_points = isset($data['reward_points']) ? (int)$data['reward_points'] : 0;
    $level = isset($data['level']) ? (int)$data['level'] : 1;

    if (empty($username)) {
        echo json_encode(['error' => 'Username is required']);
        exit;
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM rewards WHERE username = ?");
    $stmt->execute([$username]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        $stmt = $conn->prepare("UPDATE rewards SET reward_points = ?, level = ? WHERE username = ?");
        $stmt->execute([$reward_points, $level, $username]);
    } else {
        $stmt = $conn->prepare("INSERT INTO rewards (username, reward_points, level) VALUES (?, ?, ?)");
        $stmt->execute([$username, $reward_points, $level]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>