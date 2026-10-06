<?php

header('Content-Type: application/json');

$file = 'notifications.json';

if (file_exists($file)) {

    $data = file_get_contents($file);

    echo $data;

} else {

    echo json_encode([
        "error" => "Notifications file not found"
    ]);

}

?>