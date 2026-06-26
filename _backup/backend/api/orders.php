<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST,GET');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../config/database.php';

$database = new Database;
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'));

    $query = 'INSERT INTO orders SET 
              customer_name=:name, 
              customer_phone=:phone, 
              customer_email=:email, 
              products=:products, 
              total_amount=:total';

    $stmt = $db->prepare($query);

    $stmt->bindParam(':name', $data->name);
    $stmt->bindParam(':phone', $data->phone);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindParam(':products', $data->products);
    $stmt->bindParam(':total', $data->total);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['message' => 'Order berhasil disimpan!']);
    } else {
        http_response_code(503);
        echo json_encode(['message' => 'Gagal menyimpan order']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = 'SELECT * FROM orders ORDER BY created_at DESC LIMIT 50';
    }
    $stmt = $db->prepare($query);
    $stmt->execute();

    $orders_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orders_arr[] = $row;
    }

    echo json_encode($orders_arr);
}
