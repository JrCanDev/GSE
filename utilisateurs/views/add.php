<h3><b>Ajout d'un nouvel utilisateur</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <!-- Nom d'utilisateur -->
        <div class="w3-row-padding">
            <div class="w3-full">
                <label><b>Nom d'utilisateur<span style="color: red;">*</span></b></label>
                <input placeholder="Ex: Jean.Dupont" class="w3-input w3-border w3-round w3-center" type="text" name="username" required>
            </div>
        </div>

        <!-- Rôle Administrateur -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-full">
                <label class="w3-validate" style="cursor: pointer;">
                    <input class="w3-check" type="checkbox" name="admin" value="1">
                    <b>Accorder les droits d'administrateur</b>
                </label>
                <p class="w3-text-grey w3-small" style="margin-top: 5px; margin-left: 24px;">
                    Un administrateur peut gérer les matériels, lots, groupes, emprunts, statistiques et utilisateurs.
                </p>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Créer -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-blue w3-round" type="submit"
                    value="Créer l'utilisateur" name="submit">
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
