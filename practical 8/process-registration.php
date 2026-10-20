<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$terms = $_POST["terms"] ?? "";

$errors = [];

if (!preg_match("/^[A-Za-z ]{2,50}$/", $name)) {
    $errors[] = "Please enter a valid name.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    $errors[] = "Mobile number must contain exactly 10 digits.";
}

if (strlen($password) < 6) {
    $errors[] = "Password must contain at least 6 characters.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if ($course === "") {
    $errors[] = "Please select a course.";
}

if ($year === "") {
    $errors[] = "Please select a year.";
}

if ($gender === "") {
    $errors[] = "Please select your gender.";
}

if ($terms !== "Accepted") {
    $errors[] = "Please accept the terms and conditions.";
}

if (!empty($errors)) {
    echo "<h2>Registration Failed</h2>";

    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }

    echo '<p><a href="registration.html">Go Back</a></p>';

    exit;
}

$name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
$email = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
$mobile = htmlspecialchars($mobile, ENT_QUOTES, "UTF-8");
$course = htmlspecialchars($course, ENT_QUOTES, "UTF-8");
$year = htmlspecialchars($year, ENT_QUOTES, "UTF-8");
$gender = htmlspecialchars($gender, ENT_QUOTES, "UTF-8");

$file = "registrations.json";

if (!file_exists($file)) {
    file_put_contents($file, "[]");
}

$jsonData = file_get_contents($file);

$registrations = json_decode($jsonData, true);

if (!is_array($registrations)) {
    $registrations = [];
}

$newRegistration = [
    "name" => $name,
    "email" => $email,
    "mobile" => $mobile,
    "password" => password_hash($password, PASSWORD_DEFAULT),
    "course" => $course,
    "year" => $year,
    "gender" => $gender,
    "terms" => "Accepted"
];

$registrations[] = $newRegistration;

$jsonData = json_encode($registrations, JSON_PRETTY_PRINT);

if (file_put_contents($file, $jsonData, LOCK_EX) !== false) {
    echo "<h2>Registration Successful!</h2>";
    echo "<p>Your registration has been stored successfully in JSON.</p>";
    echo '<p><a href="registration.html">Register Another Student</a></p>';
} else {
    echo "<h2>Storage Error</h2>";
    echo "<p>Unable to store registration data.</p>";
}

?>