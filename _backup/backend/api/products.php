<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET,POST');

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../config/database.php';

$database = new Database;
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = 'SELECT * FROM products ORDER BY id ASC';
    $stmt = $db->prepare($query);
    $stmt->execute();

    $products_arr = [];
    $products_arr['records'] = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $product_item = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image' => $image,
        ];
        array_push($products_arr['records'], $product_item);
    }

    http_response_code(200);
    echo json_encode($products_arr);
}
