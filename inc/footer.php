</div>
<div style="left:0;"></div>
<?php
$errors = [];
$confirms = [];

if (session_status() === PHP_SESSION_ACTIVE) {
    $errors = isset($_SESSION['mesgs']['errors']) && is_array($_SESSION['mesgs']['errors']) ? $_SESSION['mesgs']['errors'] : [];
    $confirms = isset($_SESSION['mesgs']['confirm']) && is_array($_SESSION['mesgs']['confirm']) ? $_SESSION['mesgs']['confirm'] : [];

    unset($_SESSION['mesgs']['errors']);
    unset($_SESSION['mesgs']['confirm']);
}

$errors = json_encode($errors);
$confirms = json_encode($confirms);

if (isset($db) && $db) {
    $db = NULL;
}
?>

<div class="spacer"></div>

<script>
    var errors = <?= $errors ?>;
    var confirms = <?= $confirms ?>;

    setTimeout(function() {
        if (Array.isArray(errors) && errors.length > 0) {
            errors.forEach(function(error) {
                alert(error);
            });
        }
    }, 300);

    setTimeout(function() {
        if (Array.isArray(confirms) && confirms.length > 0) {
            confirms.forEach(function(confirmMsg) {
                alert(confirmMsg);
            });
        }
    }, 300);

    window.addEventListener('load', adjustSpacer);
    window.addEventListener('resize', adjustSpacer);

    function adjustSpacer() {
        var header = document.querySelector('.fullHead') || document.querySelector('.menutop');
        var footer = document.querySelector('footer');
        var content = document.querySelector('.maincontent');
        var spacer = document.querySelector('.spacer');

        if (!footer || !spacer) {
            return;
        }

        var headerHeight = header ? header.offsetHeight : 0;
        var contentHeight = content ? content.offsetHeight : 0;
        var totalUsedHeight = headerHeight + footer.offsetHeight + contentHeight;
        var windowHeight = window.innerHeight;
        var extraSpace = windowHeight - totalUsedHeight;

        spacer.style.height = extraSpace > 0 ? (extraSpace + 1) + 'px' : '0';
    }
</script>
<footer class="w3-container w3-padding-16 w3-center w3-text-white main-background-color w3-xlarge">
    <a href="https://github.com/JrCanDev/GDI" target="_blank"><i class="fab fa-github w3-hover-opacity" aria-hidden="true"></i></a>
    <p class="w3-medium">Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
    <p class="w3-medium">Hébergé par <a href="https://github.com/JrCanDev" target="_blank">JrCanDev</a></p>
</footer>

</body>

</html>