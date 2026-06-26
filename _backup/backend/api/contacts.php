<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');

include_once '../config/database.php';

$database = new Database;
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'));

    $query = 'INSERT INTO contacts SET 
              name=:name, email=:email, phone=:phone, message=:message';

    $stmt = $db->prepare($query);

    $stmt->bindParam(':name', $data->name);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindParam(':phone', $data->phone);
    $stmt->bindParam(':message', $data->message);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['message' => 'Pesan berhasil dikirim!']);
    }
}
