<?php use Models\Auth; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <base href="<?php echo BASE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Absensi Pintar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card glass">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="assets/images/logo-snt.png" alt="Logo SNT" style="width: 120px; height: auto;">
            </div>
            <h2>Welcome Back</h2>
            <p>Silakan login untuk mengakses sistem absensi.</p>
            
            <?php if(!empty($error_message)): ?>
                <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form action="auth/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Auth::getCSRFToken(); ?>">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Masukkan username">
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Masukkan password">
                </div>
                <button type="submit" class="btn-primary">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>

