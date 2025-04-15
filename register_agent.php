<?php
// Database connection and form processing at the top
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database configuration
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "support_ticket_system";
    
    // Create connection
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $fullName = htmlspecialchars($_POST['agentFullName']);
    $email = htmlspecialchars($_POST['agentEmail']);
    $phone = htmlspecialchars($_POST['agentPhone']);
    $password = $_POST['agentPassword'];
    $confirmPassword = $_POST['agentConfirmPassword'];
    
    // Initialize error/success messages
    $error = '';
    $success = '';
    
    // Validate passwords match
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Set role to 'agent'
            $role = 'agent';
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fullName, $email, $phone, $hashedPassword, $role);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
                // Clear form fields on success
                $_POST = array();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Registration - Customer Support Ticket System</title>
    <!-- External Stylesheet -->
    <link rel="stylesheet" href="formstyle.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <h1>Customer Support Ticket System</h1>
            <p>Efficiently manage and resolve transport-related issues with ease.</p>
        </div>
    </header>

    <!-- Registration Section -->
    <section class="registration-section">
        <div class="container">
            <h2>Agent Registration</h2>
            <p>Please fill in the details to create a new agent account.</p>

            <!-- Display success/error messages -->
            <?php if (!empty($success)): ?>
                <div class="alert success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <div class="registration-form">
                <form id="agentRegistrationForm" method="POST">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="agentFullName">Full Name</label>
                        <input type="text" id="agentFullName" name="agentFullName" 
                               value="<?php echo isset($_POST['agentFullName']) ? htmlspecialchars($_POST['agentFullName']) : ''; ?>" 
                               placeholder="Enter your full name" required>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="agentEmail">Email Address</label>
                        <input type="email" id="agentEmail" name="agentEmail" 
                               value="<?php echo isset($_POST['agentEmail']) ? htmlspecialchars($_POST['agentEmail']) : ''; ?>" 
                               placeholder="Enter your email" required>
                    </div>

                    <!-- Phone Number -->
                    <div class="form-group">
                        <label for="agentPhone">Phone Number</label>
                        <input type="tel" id="agentPhone" name="agentPhone" 
                               value="<?php echo isset($_POST['agentPhone']) ? htmlspecialchars($_POST['agentPhone']) : ''; ?>" 
                               placeholder="Enter your phone number" required>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="agentPassword">Password</label>
                        <input type="password" id="agentPassword" name="agentPassword" placeholder="Create a password" required>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="agentConfirmPassword">Confirm Password</label>
                        <input type="password" id="agentConfirmPassword" name="agentConfirmPassword" placeholder="Confirm your password" required>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>

                <!-- Link to Agent Login -->
                <p class="register-link">
                    Already have an account? <a href="agentlogin.php">Login Here</a>
                </p>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Customer Support Ticket System. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- JavaScript for Form Validation -->
    <script>
        document.getElementById('agentRegistrationForm').addEventListener('submit', function(event) {
            const password = document.getElementById('agentPassword').value;
            const confirmPassword = document.getElementById('agentConfirmPassword').value;

            // Check if passwords match
            if (password !== confirmPassword) {
                alert('Passwords do not match. Please try again.');
                event.preventDefault();
            }
        });
    </script>
</body>
</html>