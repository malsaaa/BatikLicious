<?php
session_start();
include("../koneksi.php");

// Check apabila data dari login form telah disubmit, isset() akan mengecek apabila terdapat data.
if ( !isset($_POST['username'], $_POST['password']) ) {
	// tidak dapat mendapatkan data yang seharusnya dikirimkan.
	exit('Please fill both the username and password fields!');
}

if ($stmt = $con->prepare('SELECT id, password FROM users WHERE username = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password);
        $stmt->fetch();
        // Account exists, now we verify the password.
        // Note: remember to use password_hash in your registration file to store the hashed passwords.
        if ($_POST['password'] === $password) {
            // Verification success! User has logged-in!
            // Create sessions, so we know the user is logged in, they basically act like cookies but remember the data on the server.
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $_POST['username'];
            $_SESSION['id'] = $id;
            echo "<script>alert('Berhasil Login!');</script>";
            // Mengubah ke halaman utama secara otomatis
            echo "<p>Redirecting...</p>";
            echo "<script>setTimeout(function(){ window.location.href = '../homepage.php'; }, 2000);</script>";
        } else {
            // Incorrect password
            echo 'Incorrect username and/or password!';
        }
    } else {
        // Incorrect username
        echo 'Incorrect username and/or password!';
    }

	$stmt->close();
}
?>