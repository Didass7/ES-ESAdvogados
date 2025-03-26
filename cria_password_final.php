<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Definir Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <form action="cria_password.php" method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
        <input type="hidden" name="ts" value="<?php echo htmlspecialchars($_GET['ts']); ?>">
        <input type="hidden" name="hash" value="<?php echo htmlspecialchars($_GET['hash']); ?>">

        <label for="password">Nova Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Definir Password</button>
    </form>

</body>
</html>