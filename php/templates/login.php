<?php $title = 'Login - IFAK Ticketsystem'; include 'templates/header.php'; ?>
<div class="login-container">
    <div class="login-box">
        <h2>Anmeldung zum Ticketsystem</h2>
        <form method="POST" action="index.php?action=login">
            <div class="form-group">
                <label for="token">Token:</label>
                <input type="text" id="token" name="token" required placeholder="Geben Sie Ihr Token ein">
            </div>
            <button type="submit" class="login-button">Anmelden</button>
        </form>
        <div class="login-help">
            <p>Bei Problemen mit dem Login wenden Sie sich an die IT-Abteilung.</p>
        </div>
    </div>
</div>
<?php include 'templates/footer.php'; ?>
