<?php
include ('./conf/config.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
	$_SESSION['username'] = $username;
	$_SESSION['password'] = $password;
    

    // Create the JSON RPC request payload
    $data = array(
        'jsonrpc' => '2.0',
        'method' => 'user.login',
        'params' => array(
            'user' => $username,
            'password' => $password
        ),
        'id' => 1,
        'auth' => null
    );

    // Encode the payload as JSON
    $payload = json_encode($data);

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json-rpc'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    // Decode the response
    $result = json_decode($response, true);

    // Check for errors
    if (isset($result['error'])) {
        $error_message = $result['error']['data'];
    } else {
        // Store the auth token in session
        $_SESSION['auth_token'] = $result['result'];
        header('Location: jobexecution.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration and Login System</title>

    <!-- Style CSS -->
    <link rel="stylesheet" href="./assets/style.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <form method="POST" action="">
    <div class="main">
		
        <!-- Login Area -->
        <div class="login" id="loginForm">
            <h4 class="text-center">BSS(Job Management System)</h4>
            <div class="login-form">
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <p class="registrationForm" onclick="showRegistrationForm()">No Account? Register Here.</p>
                    <button type="submit" class="btn btn-dark login-btn form-control">Login</button>
                </form>
            </div>
        </div>
		</form>
		<?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <!-- Bootstrap Js -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

</body>
</html>
