<?php
require "db.php";

$message = "";
$editEvent = null;
$uploadDir = __DIR__ . "/uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function deletePoster($filename) {
    $path = __DIR__ . "/uploads/" . basename($filename);

    if ($filename !== "" && is_file($path)) {
        unlink($path);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $name = trim($_POST["event_name"] ?? "");
    $date = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $id = (int)($_POST["id"] ?? 0);
    $oldPoster = "";

    if ($action === "update") {
        $check = $conn->prepare("SELECT poster FROM event_management WHERE id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existing) {
            $oldPoster = $existing["poster"] ?? "";
        } else {
            $message = "Event not found.";
            $action = "";
        }
    }

    if ($action === "add" || $action === "update") {
        if ($name === "" || $venue === "" || $date === "") {
            $message = "Please fill in all event details.";
        } elseif (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            !checkdate((int)substr($date, 5, 2), (int)substr($date, 8, 2), (int)substr($date, 0, 4))
        ) {
            $message = "Please enter a valid event date.";
        } else {
            $newPoster = $oldPoster;
            $uploadedFile = $_FILES["poster"] ?? null;

            if ($uploadedFile && $uploadedFile["error"] !== UPLOAD_ERR_NO_FILE) {
                if ($uploadedFile["error"] !== UPLOAD_ERR_OK) {
                    $message = "Poster upload failed.";
                } elseif ($uploadedFile["size"] > 2 * 1024 * 1024) {
                    $message = "Poster must be smaller than 2 MB.";
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($uploadedFile["tmp_name"]);

                    $allowedTypes = [
                        "image/jpeg" => "jpg",
                        "image/png" => "png",
                        "image/webp" => "webp"
                    ];

                    if (!isset($allowedTypes[$mime]) || getimagesize($uploadedFile["tmp_name"]) === false) {
                        $message = "Only JPG, PNG, and WEBP image files are allowed.";
                    } else {
                        $filename = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mime];
                        $destination = $uploadDir . $filename;

                        if (move_uploaded_file($uploadedFile["tmp_name"], $destination)) {
                            $newPoster = $filename;
                        } else {
                            $message = "Unable to save the uploaded poster.";
                        }
                    }
                }
            }

            if ($message === "") {
                if ($action === "add") {
                    $stmt = $conn->prepare(
                        "INSERT INTO event_management (event_name, event_date, venue, poster) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->bind_param("ssss", $name, $date, $venue, $newPoster);
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE event_management SET event_name=?, event_date=?, venue=?, poster=? WHERE id=?"
                    );
                    $stmt->bind_param("ssssi", $name, $date, $venue, $newPoster, $id);
                }

                if ($stmt->execute()) {
                    if ($action === "update" && $newPoster !== $oldPoster) {
                        deletePoster($oldPoster);
                    }

                    $message = $action === "add"
                        ? "Event added successfully."
                        : "Event updated successfully.";
                } else {
                    if ($newPoster !== $oldPoster) {
                        deletePoster($newPoster);
                    }

                    $message = "Unable to save event details.";
                }

                $stmt->close();
            }
        }
    }

    if ($action === "delete") {
        $stmt = $conn->prepare("SELECT poster FROM event_management WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($event) {
            $stmt = $conn->prepare("DELETE FROM event_management WHERE id=?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                deletePoster($event["poster"] ?? "");
                $message = "Event deleted successfully.";
            } else {
                $message = "Unable to delete event.";
            }

            $stmt->close();
        } else {
            $message = "Event not found.";
        }
    }
}

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM event_management WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editEvent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$result = $conn->query("SELECT * FROM event_management ORDER BY event_date DESC, id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f1;
            margin: 0;
            padding: 25px;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }
        h1, h2 {
            color: #2d5a27;
        }
        input, button, .button {
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        input {
            width: 100%;
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
            display: inline-block;
        }
        .delete {
            background: #b3261e;
        }
        .message {
            color: #2d5a27;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
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
        .poster {
            width: 90px;
            height: 70px;
            object-fit: cover;
        }
        @media (max-width: 700px) {
            body {
                padding: 10px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .container {
                padding: 12px;
                overflow-x: auto;
            }
            table {
                min-width: 750px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Event Management System</h1>

    <?php if ($message !== ""): ?>
        <p class="message"><?= clean($message) ?></p>
    <?php endif; ?>

    <h2><?= $editEvent ? "Update Event" : "Add Event" ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action"
               value="<?= $editEvent ? "update" : "add" ?>">

        <?php if ($editEvent): ?>
            <input type="hidden" name="id" value="<?= (int)$editEvent["id"] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <input type="text" name="event_name" placeholder="Event Name"
                   maxlength="150" required
                   value="<?= clean($editEvent["event_name"] ?? "") ?>">

            <input type="date" name="event_date" required
                   value="<?= clean($editEvent["event_date"] ?? "") ?>">

            <input type="text" name="venue" placeholder="Event Venue"
                   maxlength="150" required
                   value="<?= clean($editEvent["venue"] ?? "") ?>">

            <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp">
        </div>

        <p>Allowed poster formats: JPG, PNG, WEBP. Maximum size: 2 MB.</p>

        <?php if (!empty($editEvent["poster"])): ?>
            <p>Current poster:</p>
            <img class="poster"
                 src="uploads/<?= rawurlencode(basename($editEvent["poster"])) ?>"
                 alt="Current event poster">
        <?php endif; ?>

        <button type="submit">
            <?= $editEvent ? "Update Event" : "Add Event" ?>
        </button>

        <?php if ($editEvent): ?>
            <a class="button" href="index.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>Event Records</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Event Name</th>
            <th>Date</th>
            <th>Venue</th>
            <th>Poster</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($event = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$event["id"] ?></td>
                    <td><?= clean($event["event_name"]) ?></td>
                    <td><?= clean($event["event_date"]) ?></td>
                    <td><?= clean($event["venue"]) ?></td>
                    <td>
                        <?php if (!empty($event["poster"])): ?>
                            <img class="poster"
                                 src="uploads/<?= rawurlencode(basename($event["poster"])) ?>"
                                 alt="Event poster">
                        <?php else: ?>
                            No poster
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="button"
                           href="index.php?edit=<?= (int)$event["id"] ?>">Edit</a>

                        <form method="POST"
                              onsubmit="return confirm('Delete this event?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"
                                   value="<?= (int)$event["id"] ?>">
                            <button class="delete" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No events found. Add your first event above.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>

<?php
$conn->close();
?>