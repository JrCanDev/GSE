<!-- Navbar -->
<div class="w3-display-container top_header" style="min-height: 100px; max-height: 200px;">
  <div class="w3-padding w3-display-left logo-container">
    <img src="img/logo.png" alt="Logo" class="logo" style="height: 75px">
  </div>
  <div class="w3-padding w3-display-middle w3-center">
    <p class="title"><b><?= isset($pageTitle) ? sanitize($pageTitle) : "Gestion du système d'emprunt" ?></b></p>
  </div>
  <div class="w3-padding w3-display-topright w3-margin" style="display:flex; flex-direction: row; justify-content: center; align-items: center">
    <?php
    $displayUser = trim(sanitize($_SESSION['user']['username'] ?? ''));
    if ($displayUser === '') {
      $displayUser = sanitize($_SESSION['user']['username'] ?? 'Connecte');
    }
    if (($_SESSION['user']['username'] ?? '') == 'admin_username') {
      $displayUser = 'admin';
    }
    ?>
    <h4 class="title" style="padding-right: 20px"><?= $displayUser ?></h4>
    <img src="img/exit.png" class="clickable" alt="exit" id="disconnectImg" style="height: 50px">
  </div>
</div>

<div>
  <nav class="w3-bar w3-card">
    <a href="index.php" class="w3-bar-item w3-button headButton"><b>Accueil</b></a>

    <?php
    $list_menus = array(
      'emprunts' => 'Emprunts',
      'materiels' => 'Matériels',
      'groupes' => 'Groupes',
    );
    foreach ($list_menus as $key => $menu) {
    ?>
      <a href="index.php?element=<?= $key; ?>" class="w3-bar-item w3-button headButton"><b><?= $menu; ?></b></a>
    <?php } ?>

    <?php if (myAuthClass::checkPriviledgeDatabase($_SESSION['user']['username'])) { ?>
      <a href="index.php?element=database" class="w3-bar-item w3-button headButton"><b>Base de données</b></a>
      <!-- <form method='GET'>
        <input type='hidden' name='page' value='bd'>
        <button type='submit' class="w3-bar-item w3-button headButton">
          <b>Base de données</b>
        </button>
      </form> -->
    <?php } ?>
  </nav>
</div>

<script>
  document.getElementById('disconnectImg').addEventListener('click', function() {
    window.location.href = 'delog.php';
  });

  document.querySelectorAll('.headButton').forEach(button => {
    button.addEventListener('click', function() {
      const pageUrl = this.getAttribute('data-url')
      window.location.href = pageUrl;
    });
  });

  function adjustTextSize() {
    const textElements = document.querySelectorAll('.title');
    const screenWidth = window.innerWidth;

    let fontSize;
    if (screenWidth < 700) {
      fontSize = '0px';
    } else if (screenWidth < 800) {
      fontSize = '12px';
    } else if (screenWidth < 1100) {
      fontSize = '16px';
    } else {
      fontSize = '24px';
    }

    textElements.forEach(element => {
      element.style.fontSize = fontSize;
    });
  }

  window.addEventListener('load', adjustTextSize);
  window.addEventListener('resize', adjustTextSize);
</script>