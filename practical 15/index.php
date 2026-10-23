<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - AJAX CRUD</title>
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
        input, select, button {
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        input, select {
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .form-grid input, .form-grid select {
            width: 100%;
        }
        button {
            background: #2d5a27;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete {
            background: #b3261e;
        }
        .edit {
            background: #315f91;
        }
        .message {
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
        @media (max-width: 650px) {
            body {
                padding: 10px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .container {
                overflow-x: auto;
            }
            table {
                min-width: 650px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>StudentHub - AJAX CRUD</h1>

    <p id="message" class="message" aria-live="polite"></p>

    <h2 id="formTitle">Add Student</h2>

    <form id="studentForm">
        <input type="hidden" id="studentId">

        <div class="form-grid">
            <input type="text" id="studentName"
                   placeholder="Student Name" maxlength="100" required>

            <input type="email" id="email"
                   placeholder="Email Address" maxlength="100" required>

            <input type="text" id="department"
                   placeholder="Department" maxlength="100" required>

            <select id="semester" required>
                <option value="">Select Semester</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
                <option value="3">Semester 3</option>
                <option value="4">Semester 4</option>
                <option value="5">Semester 5</option>
                <option value="6">Semester 6</option>
                <option value="7">Semester 7</option>
                <option value="8">Semester 8</option>
            </select>
        </div>

        <button type="submit" id="saveButton">Add Student</button>
        <button type="button" id="cancelButton" hidden>Cancel Edit</button>
    </form>

    <h2>Student Records</h2>

    <input type="search" id="search"
           placeholder="Search student records">

    <button type="button" id="refreshButton">Refresh Records</button>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="studentTable">
            <tr>
                <td colspan="6">Loading records...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
const form = document.getElementById("studentForm");
const message = document.getElementById("message");
const table = document.getElementById("studentTable");
const search = document.getElementById("search");
const cancelButton = document.getElementById("cancelButton");

let students = [];

async function requestAPI(method, data = null) {
    const options = { method };

    if (data !== null) {
        options.headers = { "Content-Type": "application/json" };
        options.body = JSON.stringify(data);
    }

    const response = await fetch("api.php", options);
    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || "Request failed.");
    }

    return result;
}

function showMessage(text, success = true) {
    message.textContent = text;
    message.style.color = success ? "#2d5a27" : "#b3261e";
}

function renderStudents() {
    const term = search.value.toLowerCase().trim();

    const filtered = students.filter(student =>
        [student.student_name, student.email, student.department]
            .some(value => value.toLowerCase().includes(term))
    );

    table.replaceChildren();

    if (filtered.length === 0) {
        const row = table.insertRow();
        const cell = row.insertCell();
        cell.colSpan = 6;
        cell.textContent = "No student records found.";
        return;
    }

    filtered.forEach(student => {
        const row = table.insertRow();

        [
            student.id,
            student.student_name,
            student.email,
            student.department,
            student.semester
        ].forEach(value => {
            const cell = row.insertCell();
            cell.textContent = value;
        });

        const actions = row.insertCell();

        const editButton = document.createElement("button");
        editButton.textContent = "Edit";
        editButton.className = "edit";
        editButton.type = "button";
        editButton.addEventListener("click", () => editStudent(student.id));

        const deleteButton = document.createElement("button");
        deleteButton.textContent = "Delete";
        deleteButton.className = "delete";
        deleteButton.type = "button";
        deleteButton.addEventListener("click", () => deleteStudent(student.id));

        actions.append(editButton, document.createTextNode(" "), deleteButton);
    });
}

async function loadStudents() {
    try {
        const result = await requestAPI("GET");
        students = result.data;
        renderStudents();
    } catch (error) {
        showMessage(error.message, false);
        table.replaceChildren();
        const row = table.insertRow();
        const cell = row.insertCell();
        cell.colSpan = 6;
        cell.textContent = "Unable to load student records.";
    }
}

form.addEventListener("submit", async event => {
    event.preventDefault();

    const id = document.getElementById("studentId").value;

    const data = {
        action: id ? "update" : "add",
        student_name: document.getElementById("studentName").value.trim(),
        email: document.getElementById("email").value.trim(),
        department: document.getElementById("department").value.trim(),
        semester: Number(document.getElementById("semester").value)
    };

    if (id) {
        data.id = Number(id);
    }

    try {
        const result = await requestAPI("POST", data);
        showMessage(result.message);
        form.reset();
        document.getElementById("studentId").value = "";
        document.getElementById("formTitle").textContent = "Add Student";
        document.getElementById("saveButton").textContent = "Add Student";
        cancelButton.hidden = true;
        await loadStudents();
    } catch (error) {
        showMessage(error.message, false);
    }
});

function editStudent(id) {
    const student = students.find(item => Number(item.id) === Number(id));

    if (!student) {
        showMessage("Student not found.", false);
        return;
    }

    document.getElementById("studentId").value = student.id;
    document.getElementById("studentName").value = student.student_name;
    document.getElementById("email").value = student.email;
    document.getElementById("department").value = student.department;
    document.getElementById("semester").value = student.semester;
    document.getElementById("formTitle").textContent = "Update Student";
    document.getElementById("saveButton").textContent = "Update Student";
    cancelButton.hidden = false;
    showMessage("Edit the details and click Update Student.");
    form.scrollIntoView({ behavior: "smooth" });
}

async function deleteStudent(id) {
    if (!confirm("Are you sure you want to delete this student?")) {
        return;
    }

    try {
        const result = await requestAPI("POST", {
            action: "delete",
            id: Number(id)
        });

        showMessage(result.message);
        await loadStudents();
    } catch (error) {
        showMessage(error.message, false);
    }
}

cancelButton.addEventListener("click", () => {
    form.reset();
    document.getElementById("studentId").value = "";
    document.getElementById("formTitle").textContent = "Add Student";
    document.getElementById("saveButton").textContent = "Add Student";
    cancelButton.hidden = true;
    showMessage("");
});

search.addEventListener("input", renderStudents);
document.getElementById("refreshButton").addEventListener("click", loadStudents);

loadStudents();
</script>
</body>
</html>