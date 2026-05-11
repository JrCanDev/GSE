<h3><b>Modification d'un groupe</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <input type="hidden" name="id_groupe" value="<?= $groupe->id_groupe ?>">

        <!-- Nom et Date de restitution -->
        <div class="w3-row-padding">
            <div class="w3-half">
                <label><b>Nom du groupe<span style="color: red;">*</span></b></label>
                <input placeholder="BUT 2 FI" value="<?= $groupe->nom_groupe ?>" class="w3-input w3-border w3-round w3-center" type="text" name="nom_groupe" required>
            </div>
            <div class="w3-half">
                <label><b>Date de restitution<span style="color: red;">*</span></b></label>
                <input value="<?= $groupe->date_restitution ?>" class="w3-input w3-border w3-round w3-center" type="date" name="date_restitution" required>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Créer -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-teal w3-round" type="submit"
                    value="Mettre à jour le groupe" name="update">
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