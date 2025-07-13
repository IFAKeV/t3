</main>
<footer>
    <p>&copy; <?php echo date('Y'); ?> - IFAK e.V. Ticketsystem | Adressbuch-Stand: <?php echo htmlspecialchars(get_addressbook_date()); ?></p>
</footer>
<script>
    window.BASE_URL = "<?php echo $base_url; ?>";
    window.PHP_BASE = "<?php echo $php_base; ?>";
</script>
<script src="<?php echo $base_url; ?>/static/js/main.js"></script>
<script src="<?php echo $base_url; ?>/static/js/autocomplete.js"></script>
</body>
</html>
