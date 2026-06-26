<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET,POST');

include_once '../config/database.php';

$database = new Database;
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = 'SELECT * FROM testimonials WHERE approved = 1 ORDER BY created_at DESC';
    $stmt = $db->prepare($query);
    $stmt->execute();

    $testimonials_arr = [];
    $testimonials_arr['records'] = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $testimonial_item = [
            'id' => $id,
            'name' => $name,
            'occupation' => $occupation,
            'content' => $content,
            'rating' => $rating,
        ];
        array_push($testimonials_arr['records'], $testimonial_item);
    }

    http_response_code(200);
    echo json_encode($testimonials_arr);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'));

    if (! empty($data->name) && ! empty($data->content)) {
        $query = 'INSERT INTO testimonials SET 
                  name=:name, 
                  occupation=:occupation, 
                  content=:content, 
                  rating=:rating, 
                  approved=1';

        $stmt = $db->prepare($query);

        $stmt->bindParam(':name', $data->name);
        $stmt->bindParam(':occupation', $data->occupation);
        $stmt->bindParam(':content', $data->content);
        $stmt->bindParam(':rating', $data->rating);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['message' => 'Testimoni berhasil dikirim!']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Gagal mengirim testimoni']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Data tidak lengkap']);
    }
}
