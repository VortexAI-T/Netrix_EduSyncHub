<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli('fdb1034.awardspace.net', '4669157_educ', 'Ilovemyself', '4669157_educ');

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$result = $conn->query('SELECT username, fullname, reward_points, level FROM users ORDER BY reward_points DESC');
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = [
        'username' => $row['username'],
        'fullname' => $row['fullname'],
        'rewardPoints' => (int)$row['reward_points'],
        'level' => (int)$row['level']
    ];
}

echo json_encode($users);

$result->free();
$conn->close();
?>