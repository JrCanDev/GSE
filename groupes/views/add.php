<h3><b>Ajout d'un nouveau groupe</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <!-- Prénom, Nom et Année BUT -->
        <div class="w3-row-padding">
            <div class="w3-half">
                <label><b>Nom du groupe<span style="color: red;">*</span></b></label>
                <input placeholder="BUT 2 FI" class="w3-input w3-border w3-round w3-center" type="text" name="nom_groupe" required>
            </div>
            <div class="w3-half">
                <label><b>Date de restitution<span style="color: red;">*</span></b></label>
                <input value="<?= date('Y-m-d') ?>" class="w3-input w3-border w3-round w3-center" type="date" name="date_restitution" required>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Créer -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-teal w3-round" type="submit"
                    value="Ajouter le groupe" name="submit">
            </div>

            <!-- Bouton Annuler -->
            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit"
                    value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            document.querySelector("input[name='nom_groupe']").focus();
        </script>
    </form>
</div>