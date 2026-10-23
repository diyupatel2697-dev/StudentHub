<?php
require "db.php";

$message = "";
$editStudent = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $name = trim($_POST["student_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $semester = (int)($_POST["semester"] ?? 0);

    if ($action === "add" || $action === "update") {
        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $department === "" || $semester < 1 || $semester > 8) {
            $message = "Please enter valid student details.";
        } else {
            if ($action === "add") {
                $stmt = $conn->prepare(
                    "INSERT INTO student_records (student_name, email, department, semester) VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param("sssi", $name, $email, $department, $semester);
            } else {
                $id = (int)($_POST["id"] ?? 0);
                $stmt = $conn->prepare(
                    "UPDATE student_records SET student_name=?, email=?, department=?, semester=? WHERE id=?"
                );
                $stmt->bind_param("sssii", $name, $email, $department, $semester, $id);
            }

            if ($stmt->execute()) {
                $message = $action === "add"
                    ? "Student added successfully."
                    : "Student updated successfully.";
            } else {
                $message = $stmt->errno === 1062
                    ? "This email already exists."
                    : "Unable to save student details.";
            }

            $stmt->close();
        }
    }

    if ($action === "delete") {
        $id = (int)($_POST["id"] ?? 0);
        $stmt = $conn->prepare("DELETE FROM student_records WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Student deleted successfully.";
        } else {
            $message = "Student not found or could not be deleted.";
        }

        $stmt->close();
    }
}

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM student_records WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editStudent = $result->fetch_assoc();
    $stmt->close();
}

$search = trim($_GET["search"] ?? "");
$filterSemester = (int)($_GET["semester"] ?? 0);

$sql = "SELECT * FROM student_records WHERE
        (student_name LIKE ? OR email LIKE ? OR department LIKE ?)";

$searchValue = "%" . $search . "%";

if ($filterSemester >= 1 && $filterSemester <= 8) {
    $sql .= " AND semester = ?";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

if ($filterSemester >= 1 && $filterSemester <= 8) {
    $stmt->bind_param("sssi", $searchValue, $searchValue, $searchValue, $filterSemester);
} else {
    $stmt->bind_param("sss", $searchValue, $searchValue, $searchValue);
}

$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f1;
            margin: 0;
            padding: 25px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }
        h1, h2 {
            color: #2d5a27;
        }
        input, select, button, .button {
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        input, select {
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button, .button {
            background: #2d5a27;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .delete {
            background: #b3261e;
        }
        .message {
            color: #2d5a27;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #2d5a27;
            color: white;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .form-grid input, .form-grid select {
            width: 100%;
        }
        @media (max-width: 650px) {
            body {
                padding: 10px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 6px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Student Management System</h1>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <h2><?= $editStudent ? "Update Student" : "Add Student" ?></h2>

    <form method="POST" action="index.php">
        <input type="hidden" name="action"
               value="<?= $editStudent ? "update" : "add" ?>">

        <?php if ($editStudent): ?>
            <input type="hidden" name="id"
                   value="<?= (int)$editStudent["id"] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <input type="text" name="student_name"
                   placeholder="Student Name" required maxlength="100"
                   value="<?= htmlspecialchars($editStudent["student_name"] ?? "", ENT_QUOTES, "UTF-8") ?>">

            <input type="email" name="email"
                   placeholder="Email Address" required maxlength="100"
                   value="<?= htmlspecialchars($editStudent["email"] ?? "", ENT_QUOTES, "UTF-8") ?>">

            <input type="text" name="department"
                   placeholder="Department" required maxlength="100"
                   value="<?= htmlspecialchars($editStudent["department"] ?? "", ENT_QUOTES, "UTF-8") ?>">

            <select name="semester" required>
                <option value="">Select Semester</option>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?= $i ?>"
                        <?= (int)($editStudent["semester"] ?? 0) === $i ? "selected" : "" ?>>
                        Semester <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit">
            <?= $editStudent ? "Update Student" : "Add Student" ?>
        </button>

        <?php if ($editStudent): ?>
            <a class="button" href="index.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>Search and Filter Students</h2>

    <form method="GET" action="index.php">
        <input type="text" name="search"
               placeholder="Search name, email or department"
               value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>">

        <select name="semester">
            <option value="0">All Semesters</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?= $i ?>"
                    <?= $filterSemester === $i ? "selected" : "" ?>>
                    Semester <?= $i ?>
                </option>
            <?php endfor; ?>
        </select>

        <button type="submit">Search</button>
        <a class="button" href="index.php">Reset</a>
    </form>

    <h2>Student Records</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Semester</th>
            <th>Actions</th>
        </tr>

        <?php if ($students->num_rows > 0): ?>
            <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$student["id"] ?></td>
                    <td><?= htmlspecialchars($student["student_name"], ENT_QUOTES, "UTF-8") ?></td>
                    <td><?= htmlspecialchars($student["email"], ENT_QUOTES, "UTF-8") ?></td>
                    <td><?= htmlspecialchars($student["department"], ENT_QUOTES, "UTF-8") ?></td>
                    <td><?= (int)$student["semester"] ?></td>
                    <td>
                        <a class="button"
                           href="index.php?edit=<?= (int)$student["id"] ?>">Edit</a>

                        <form method="POST" action="index.php"
                              onsubmit="return confirm('Delete this student?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"
                                   value="<?= (int)$student["id"] ?>">
                            <button class="delete" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No student records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>