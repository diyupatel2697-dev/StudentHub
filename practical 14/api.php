<?php
header("Content-Type: application/json; charset=UTF-8");

require "db.php";

function respond($success, $message, $data = null) {
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $result = $conn->query(
        "SELECT id, student_name, email, department, semester
         FROM student_records ORDER BY id DESC"
    );

    if (!$result) {
        respond(false, "Unable to fetch records.");
    }

    $students = [];

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    respond(true, "Students fetched successfully.", $students);
}

if ($method !== "POST") {
    http_response_code(405);
    respond(false, "Method not allowed.");
}

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    respond(false, "Invalid JSON data.");
}

$action = $input["action"] ?? "";

if ($action === "add" || $action === "update") {
    $name = trim($input["student_name"] ?? "");
    $email = trim($input["email"] ?? "");
    $department = trim($input["department"] ?? "");
    $semester = filter_var(
        $input["semester"] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        $name === "" ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 100 ||
        $department === "" ||
        strlen($department) > 100 ||
        $semester === false ||
        $semester === null ||
        $semester < 1 ||
        $semester > 8
    ) {
        respond(false, "Please enter valid student details.");
    }

    if ($action === "add") {
        $stmt = $conn->prepare(
            "INSERT INTO student_records
            (student_name, email, department, semester)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param("sssi", $name, $email, $department, $semester);
    } else {
        $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            respond(false, "Invalid student ID.");
        }

        $stmt = $conn->prepare(
            "UPDATE student_records
             SET student_name=?, email=?, department=?, semester=?
             WHERE id=?"
        );

        $stmt->bind_param("sssii", $name, $email, $department, $semester, $id);
    }

    if (!$stmt) {
        respond(false, "Unable to prepare database query.");
    }

    if ($stmt->execute()) {
        if ($action === "update" && $stmt->affected_rows === 0) {
            $check = $conn->prepare(
                "SELECT id FROM student_records WHERE id=?"
            );
            $check->bind_param("i", $id);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();

            if (!$exists) {
                $stmt->close();
                respond(false, "Student not found.");
            }
        }

        $message = $action === "add"
            ? "Student added successfully."
            : "Student updated successfully.";

        $stmt->close();
        respond(true, $message);
    }

    $duplicate = $stmt->errno === 1062;
    $stmt->close();

    respond(false, $duplicate
        ? "This email already exists."
        : "Unable to save student details.");
}

if ($action === "delete") {
    $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        respond(false, "Invalid student ID.");
    }

    $stmt = $conn->prepare("DELETE FROM student_records WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        respond(true, "Student deleted successfully.");
    }

    $stmt->close();
    respond(false, "Student not found.");
}

respond(false, "Invalid action.");
?>