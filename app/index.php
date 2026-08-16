<?php



$conn = new mysqli(

&#x20;   getenv("DB\_HOST"),

&#x20;   getenv("DB\_USER"),

&#x20;   getenv("DB\_PASS"),

&#x20;   getenv("DB\_NAME")

);



if ($conn->connect\_error) {

&#x20;   die("DB Connection Failed");

}



$conn->query("

CREATE TABLE IF NOT EXISTS employees (

id INT AUTO\_INCREMENT PRIMARY KEY,

name VARCHAR(50),

designation VARCHAR(50)

)

");



if ($\_SERVER\["REQUEST\_METHOD"] == "POST") {



&#x20;   $name = $\_POST\["name"];

&#x20;   $designation = $\_POST\["designation"];



&#x20;   $stmt = $conn->prepare(

&#x20;       "INSERT INTO employees(name,designation) VALUES (?,?)"

&#x20;   );



&#x20;   $stmt->bind\_param("ss", $name, $designation);

&#x20;   $stmt->execute();

}



$result = $conn->query("SELECT \* FROM employees");


<h2>Employee App</h2>

<form method="post">

Name:
<input name="name" required>

Designation:
<input name="designation" required>

<button>Add</button>

</form>

<hr>

<?php



while ($row = $result->fetch\_assoc()) {



&#x20;   echo $row\["name"] .

&#x20;        " - " .

&#x20;        $row\["designation"] .

&#x20;        "<br>";

}

