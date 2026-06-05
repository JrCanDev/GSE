<h3><b>Modification de l'utilisateur</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <input type="hidden" name="id_user" value="<?= $user->id ?>">

        <!-- Nom d'utilisateur -->
        <div class="w3-row-padding">
            <div class="w3-full">
                <label><b>Nom d'utilisateur<span style="color: red;">*</span></b></label>
                <input placeholder="Ex: Jean.Dupont" value="<?= sanitize($user->username) ?>" class="w3-input w3-border w3-round w3-center" type="text" name="username" required>
            </div>
        </div>

        <!-- Rôle Administrateur -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-full">
                <?php if ($isSelf): ?>
                    <input type="hidden" name="admin" value="1">
                    <label class="w3-validate w3-text-grey" style="cursor: not-allowed;">
                        <input class="w3-check" type="checkbox" checked disabled>
                        <b>Accorder les droits d'administrateur</b> (Vous ne pouvez pas révoquer vos propres droits)
                    </label>
                <?php else: ?>
                    <label class="w3-validate" style="cursor: pointer;">
                        <input class="w3-check" type="checkbox" name="admin" value="1" <?= $user->admin ? 'checked' : '' ?>>
                        <b>Accorder les droits d'administrateur</b>
                    </label>
                <?php endif; ?>
            </div>
        </div>

        <!-- Réinitialisation du mot de passe -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-full">
                <?php if ($isSelf): ?>
                    <label class="w3-validate w3-text-grey" style="cursor: not-allowed;">
                        <input class="w3-check" type="checkbox" disabled>
                        <b>Réinitialiser le mot de passe</b> (Vous ne pouvez pas réinitialiser votre propre mot de passe)
                    </label>
                <?php else: ?>
                    <label class="w3-validate" style="cursor: pointer;">
                        <input class="w3-check" type="checkbox" name="reset_password" value="1">
                        <b>Réinitialiser le mot de passe</b> (l'utilisateur devra le redéfinir à sa prochaine connexion)
                    </label>
                <?php endif; ?>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Modifier -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-blue w3-round" type="submit"
                    value="Mettre à jour" name="update">
            </div>

            <!-- Bouton Annuler -->
            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit"
                    value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            document.querySelector("input[name='username']").focus();
        </script>
    </form>
</div>
