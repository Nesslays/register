<?php
// Function to generate a random 5-digit code
function generateRandomCode() {
    return mt_rand(10000, 99999);
}

// Function to validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number format
function isValidPhoneNumber($phone) {
    // Assuming the phone number format is +[CountryCode][Number]
    return preg_match('/^\+[0-9]{1,3}[0-9]{6,14}$/', $phone);
}

// Function to validate password format for proper security
function isValidPassword($password) {
    // Password must contain at least one uppercase, one lowercase, one digit, and one special character
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

// Your database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbc_academy_db";

// Create a connection to the database
try {
    $conn = new PDO("mysql:host=$localhost;dbname=$cbc_academy_db", $root, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $datetime = date("Y-m-d H:i:s");
    $random_code = generateRandomCode();

    // Validate email and phone number
    if (!isValidEmail($email)) {
        die("Invalid email format.");
    }
    if (!isValidPhoneNumber($phone)) {
        die("Invalid phone number format.");
    }

    // Check if email or phone number already exists in the database
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE email = :email OR phone = :phone");
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone", $phone);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result["count"] > 0) {
        die("Email or phone number already exists.");
    }

    // Validate password
    if (!isValidPassword($password)) {
        die("Password must contain at least one uppercase, one lowercase, one digit, and one special character, and be at least 8 characters long.");
    }

    // Hash the password before storing it in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into the database
    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, phone, password, registration_date, random_code) VALUES (:firstname, :lastname, :email, :phone, :password, :registration_date, :random_code)");
    $stmt->bindParam(":firstname", $firstname);
    $stmt->bindParam(":lastname", $lastname);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":password", $hashed_password);
    $stmt->bindParam(":registration_date", $datetime);
    $stmt->bindParam(":random_code", $random_code);

    try {
        $stmt->execute();
        echo "Registration successful!";
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
</head>
<body>
    <h2>Sign-up</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="firstname">First Name:</label>
        <input type="text" name="firstname" required><br>

        <label for="lastname">Last Name:</label>
        <input type="text" name="lastname" required><br>

        <label for="email">User Email:</label>
        <input type="email" name="email" required><br>

        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <label for="confirm_password">Password Cornfarmation :</label>
        <input type="password" name="confirm_password" required><br>

        <input type="submit" value="Register">
        <input type="reset" value="Reset">
        
        <hr>
        
    </form>
</body>
</html>
