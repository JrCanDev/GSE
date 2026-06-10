<a href="index.php?element=utilisateurs&action=add" class="w3-margin w3-button w3-border w3-round">
    <b>Ajouter un nouvel utilisateur</b>
</a>

<?php $totalUsers = $utilisateurs ? count($utilisateurs) : 0; ?>

<div class="w3-container w3-margin-bottom w3-margin-top">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarUser"
        onkeyup="filtrerUtilisateurs()"
        placeholder="Rechercher un utilisateur par son nom...">
</div>

<h2><b>Utilisateurs</b></h2>
<table class="w3-table w3-striped w3-small w3-bordered w3-border">
    <thead>
        <tr class="w3-blue">
            <th>Nom d'utilisateur <span class="sort-arrow"></span></th>
            <th>Rôle <span class="sort-arrow"></span></th>
            <th>Statut de connexion <span class="sort-arrow"></span></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($utilisateurs): ?>
            <?php foreach ($utilisateurs as $user): ?>
                <?php
                $isSelf = ((int)$user->id === (int)($_SESSION['user']['id'] ?? -1));
                $needsPasswordSetup = (empty($user->password));
                $statusText = $needsPasswordSetup ? "Première connexion requise" : "Actif";
                $statusColor = $needsPasswordSetup ? "w3-text-red" : "w3-text-green";
                ?>
                <tr class="item-user">
                    <td><?= sanitize($user->username) ?> <?= $isSelf ? "<b>(Vous)</b>" : "" ?></td>
                    <td><?= $user->admin ? "Administrateur" : "Utilisateur" ?></td>
                    <td class="<?= $statusColor ?>"><?= $statusText ?></td>
                    <td>
                        <div>
                            <form action="?element=utilisateurs&action=card" method="post">
                                <input type="hidden" name="id_user" value="<?= $user->id ?>">
                                <input type="submit" name="edit" class="w3-button w3-small w3-border w3-round" value="✏️">
                            </form>

                            <?php if (!$isSelf): ?>
                                <form action="?element=utilisateurs" method="post"
                                    onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur (<?= sanitize($user->username) ?>) ?');">
                                    <input type="hidden" name="id" value="<?= $user->id ?>">
                                    <input type="submit" name="delete" class="w3-button w3-small w3-border w3-round" value="🗑️">
                                </form>
                            <?php else: ?>
                                <button class="w3-button w3-small w3-border w3-round w3-disabled" title="Vous ne pouvez pas vous supprimer vous-même" disabled>🗑️</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Aucun utilisateur trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<h2 id="compteurUsers"><?= $totalUsers ?> / <?= $totalUsers ?> utilisateur(s) trouvé(s)</h2>

<script>
    function filtrerUtilisateurs() {
        let input = document.getElementById('searchBarUser').value.toLowerCase();
        let rows = document.getElementsByClassName('item-user');
        let compteurUsers = document.getElementById('compteurUsers');
        let totalUsersCount = <?= $totalUsers ?>;
        let usersVisibles = 0;

        for (let i = 0; i < rows.length; i++) {
            let texteLigne = rows[i].textContent || rows[i].innerText;
            if (texteLigne.toLowerCase().indexOf(input) > -1) {
                rows[i].style.display = '';
                usersVisibles++;
            } else {
                rows[i].style.display = 'none';
            }
        }

        if (compteurUsers) {
            compteurUsers.textContent = usersVisibles + ' / ' + totalUsersCount + ' utilisateur(s) trouvé(s)';
        }
    }
</script>