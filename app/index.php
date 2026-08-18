<?php

$conn = new mysqli(
    getenv("DB_HOST"),
    getenv("DB_USER"),
    getenv("DB_PASS"),
    getenv("DB_NAME")
);

if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}

$conn->query("
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50),
    designation VARCHAR(50)
)
");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $designation = $_POST["designation"];

    $stmt = $conn->prepare(
        "INSERT INTO employees(name, designation) VALUES (?, ?)"
    );

    $stmt->bind_param("ss", $name, $designation);
    $stmt->execute();
}

$result = $conn->query("SELECT * FROM employees");

?>

<h2>Employee App</h2>

<form method="post">

    Name:
    <input name="name" required>

    <br><br>

    Designation:
    <input name="designation" required>

    <br><br>

    <button type="submit">Add</button>

</form>

<hr>

<?php

while ($row = $result->fetch_assoc()) {

    echo $row["name"] .
         " - " .
         $row["designation"] .
         "<br>";
}

?>

