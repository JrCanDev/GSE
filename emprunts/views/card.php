<h3><b>Modification d'un emprunt</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">

        <!-- Prénom, Nom et Année BUT -->
        <div class="w3-row-padding">
            <div class="w3-third">
                <label><b>Nom</b></label>
                <input class="w3-input w3-border w3-round w3-center" style="opacity: 0.5;"
                    type="text" value="<?= sanitize($emprunt->nom_emprunteur) ?>" readonly>
            </div>
            <div class="w3-third">
                <label><b>Prénom</b></label>
                <input class="w3-input w3-border w3-round w3-center" style="opacity: 0.5;"
                    type="text" value="<?= sanitize($emprunt->prenom_emprunteur) ?>" readonly>
            </div>
            <div class="w3-third">
                <label><b>Année</b></label>
                <select class="w3-select w3-border w3-round w3-center" style="opacity: 0.5;" name="id_groupe" required>
                    <option value="<?= $emprunt->id_groupe ?>" selected><?= sanitize($emprunt->nom_groupe) ?></option>
                </select>
            </div>
        </div>

        <!-- Caution, Date d’emprunt et Date de restitution prévue -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-third">
                <label><b>Caution<span style="color: red;">*</span></b></label>
                <select class="w3-select w3-border w3-round w3-center" name="caution" required>
                    <?php foreach (Emprunt::$cautions as $caution): ?>
                        <option value="<?= $caution ?>" <?= $emprunt->caution === $caution ? 'selected' : '' ?>><?= $caution ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w3-third">
                <label><b>Date d’emprunt</b></label>
                <input class="w3-input w3-border w3-round w3-center" type="date" style="opacity: 0.5;"
                    name="date_emprunt" value="<?= sanitize($emprunt->date_emprunt) ?>" readonly>
            </div>
            <div class="w3-third">
                <label><b>Date de restitution prévue<span style="color: red;">*</span></b></label>
                <input class="w3-input w3-border w3-round w3-center" type="date"
                    name="date_prevue_restitution" value="<?= sanitize($emprunt->date_prevue_restitution) ?>" required>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <label><b>Date de restitution réelle</b></label>
            <input class="w3-input w3-border w3-round w3-center" type="date"
                name="date_reelle_restitution" value="<?= sanitize($emprunt->date_reelle_restitution) ?>">
        </div>

        <!-- Remarque -->
        <div class="w3-margin-top">
            <label><b>Remarque</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="remarque"
                placeholder="C'est certainement une superbe remarque" rows="1"><?= sanitize($emprunt->remarque) ?></textarea>
        </div>

        <?php if ($emprunt->date_reelle_restitution): ?>
            <div class="w3-row-padding w3-margin-top">
                <div class="w3-half">
                    <label><b>État de restitution</b></label>

                    <select class="w3-select w3-border w3-round w3-center" name="etat_restitution" required>
                        <?php foreach (Materiel::$etats as $etat): ?>
                            <option value="<?= htmlspecialchars($etat) ?>" <?= $emprunt->etat_restitution === $etat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($etat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w3-half">
                    <label><b>Remarque de restitution</b></label>
                    <input class="w3-input w3-border w3-round w3-center" type="text" name="remarque_restitution"
                        value="<?= sanitize($emprunt->remarque_restitution) ?>">
                </div>
            </div>
        <?php endif; ?>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Mettre à jour -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-teal w3-round" type="submit"
                    value="Mettre à jour l'emprunt" name="update">
            </div>

            <!-- Bouton Annuler -->
            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit"
                    value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            document.querySelector("input[name='date_emprunt']").focus();
        </script>
    </form>
</div>