<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$rewardPoints = isset($input['rewardPoints']) ? (int)$input['rewardPoints'] : 0;
$level = isset($input['level']) ? (int)$input['level'] : 1;

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is required']);
    exit;
}

$conn = new mysqli('fdb1034.awardspace.net', '4669157_educ', 'Ilovemyself', '4669157_educ');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare('UPDATE users SET reward_points = ?, level = ? WHERE username = ?');
$stmt->bind_param('iis', $rewardPoints, $level, $username);
$success = $stmt->execute();

echo json_encode([
    'success' => $success,
    'message' => $success ? 'User updated successfully' : 'Failed to update user'
]);

$stmt->close();
$conn->close();
?>