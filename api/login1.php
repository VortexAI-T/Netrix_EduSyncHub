<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is required']);
    exit;
}

$conn = new mysqli('fdb1034.awardspace.net', '4669157_educ', 'Ilovemyself', '4669157_educ');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare('SELECT fullname, reward_points, level FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
echo json_encode([
    'success' => true,
    'username' => $username,
    'fullname' => $user['fullname'],
    'reward_points' => (int)$user['reward_points'],
    'level' => (int)$user['level']
]);

$stmt->close();
$conn->close();
?>