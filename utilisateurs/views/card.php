<h3><b>Modification de l'utilisateur</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <input type="hidden" name="id_user" value="<?= $user->id ?>">

        <div class="w3-row-padding">
            <div class="w3-full">
                <label><b>Nom d'utilisateur<span style="color: red;">*</span></b></label>
                <input placeholder="Ex: Jean.Dupont" value="<?= sanitize($user->username) ?>" class="w3-input w3-border w3-round w3-center" type="text" name="username" required>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <div class="w3-full">
                <label><b>Entité<span style="color: red;">*</span></b></label>
                <select class="w3-select w3-border w3-round w3-center" name="entite_id" id="entiteSelect" required>
                    <?php foreach ($entites as $entite): ?>
                        <option <?= intval($user->entite_id) === intval($entite->id) ? 'selected' : '' ?> value="<?= $entite->id ?>"><?= sanitize($entite->nom) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <div class="w3-full">
                <?php if ($isSelf): ?>
                    <input type="hidden" name="admin" value="1" id="adminCheckbox">
                    <label class="w3-validate w3-text-grey" style="cursor: not-allowed;">
                        <input class="w3-check" type="checkbox" checked disabled>
                        <b>Accorder les droits d'administrateur</b> (Vous ne pouvez pas révoquer vos propres droits)
                    </label>
                <?php else: ?>
                    <label class="w3-validate" style="cursor: pointer;">
                        <input class="w3-check" type="checkbox" name="admin" id="adminCheckbox" value="1" <?= $user->admin ? 'checked' : '' ?> onchange="gererDroitsAdmin()">
                        <b>Accorder les droits d'administrateur</b>
                    </label>
                <?php endif; ?>
            </div>
        </div>

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
            <div class="w3-half w3-right">
                <input class="w3-button w3-blue w3-round" type="submit" value="Mettre à jour" name="update">
            </div>

            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit" value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            document.querySelector("input[name='username']").focus();

            function gererDroitsAdmin() {
                const checkbox = document.getElementById('adminCheckbox');
                const select = document.getElementById('entiteSelect');
                const options = select.options;

                const estAdmin = checkbox.type === 'checkbox' ? checkbox.checked : checkbox.value === '1';

                if (estAdmin) {
                    select.value = "0";

                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value !== "0") {
                            options[i].disabled = true;
                        }
                    }
                } else {
                    for (let i = 0; i < options.length; i++) {
                        options[i].disabled = false;
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                gererDroitsAdmin();
            });
        </script>
    </form>
</div>