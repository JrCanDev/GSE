<div class="dtitle w3-container w3-teal">
    Fiche étudiant
</div>
<div class="col-2">
</div>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="POST" class="w3-container w3-card-4 w3-padding">

        <!-- Numéro étudiant -->
        <label class="w3-text-teal"><b>Numéro étudiant</b></label>
        <input class="w3-input w3-border w3-round" type="text" name="numetu"
            value="<?= htmlspecialchars($etudiant->numetu) ?>" required>

        <!-- Prénom et Nom -->
        <div class="w3-row-padding">
            <div class="w3-half">
                <label class="w3-text-teal"><b>Prénom</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="firstname"
                    value="<?= htmlspecialchars($etudiant->firstname) ?>" required>
            </div>
            <div class="w3-half">
                <label class="w3-text-teal"><b>Nom</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="lastname"
                    value="<?= htmlspecialchars($etudiant->lastname) ?>" required>
            </div>
        </div>

        <!-- Date de naissance et Année -->
        <div class="w3-row-padding">
            <div class="w3-half">
                <label class="w3-text-teal"><b>Date de naissance</b></label>
                <input class="w3-input w3-border w3-round" type="date" name="birthday"
                    value="<?= htmlspecialchars($etudiant->birthday) ?>" required>
            </div>
            <div class="w3-half">
                <label class="w3-text-teal"><b>Année</b></label>
                <input class="w3-input w3-border w3-round" type="number" name="year"
                    min="1" max="10" value="<?= htmlspecialchars($etudiant->year) ?>" required>
            </div>
        </div>

        <!-- Diplôme -->
        <label class="w3-text-teal"><b>Diplôme</b></label>
        <select class="w3-select w3-border w3-round" name="diploma" required>
            <?php foreach (Etudiant::$diplomas as $diploma): ?>
                <option value="<?= htmlspecialchars($diploma) ?>"
                    <?= $etudiant->diploma === $diploma ? 'selected' : '' ?>>
                    <?= htmlspecialchars($diploma) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- TD et TP -->
        <div class="w3-row-padding">
            <div class="w3-half">
                <label class="w3-text-teal"><b>TD</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="td"
                    value="<?= htmlspecialchars($etudiant->td) ?>">
            </div>
            <div class="w3-half">
                <label class="w3-text-teal"><b>TP</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="tp"
                    value="<?= htmlspecialchars($etudiant->tp) ?>">
            </div>
        </div>

        <!-- Adresse, Code postal et Ville -->
        <div class="w3-row-padding">
            <div class="w3-half">
                <label class="w3-text-teal"><b>Adresse</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="address"
                    value="<?= htmlspecialchars($etudiant->address) ?>">
            </div>
            <div class="w3-quarter">
                <label class="w3-text-teal"><b>Code postal</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="zipcode"
                    value="<?= htmlspecialchars($etudiant->zipcode) ?>">
            </div>
            <div class="w3-quarter">
                <label class="w3-text-teal"><b>Ville</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="town"
                    value="<?= htmlspecialchars($etudiant->town) ?>">
            </div>
        </div>

        <br>
        <div class="w3-row">
            <input class="w3-button w3-teal w3-round" type="submit"
                value="Mettre à jour" name="update">
        </div>

        <script>
            document.querySelector("input[name='numetu']").focus();
        </script>
    </form>
</div>

<!-- Section des classements par module -->
<?php if (!empty($classements)): ?>
<div class="col-2 w3-container" style="margin-top: 20px;">
    <div class="w3-container w3-card-4 w3-padding">
        <h3 class="w3-text-teal">Classements de l'étudiant par module</h3>
        
        <table class="w3-table w3-striped w3-bordered w3-border w3-hoverable">
            <thead>
                <tr class="w3-teal">
                    <th>Module</th>
                    <th>Moyenne / 20</th>
                    <th>Rang</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classements as $entry): ?>
                    <tr>
                        <td><?= htmlspecialchars($entry['module']->name) ?></td>
                        <td><?= number_format($entry['moyenne'], 2) ?></td>
                        <td><strong><?= $entry['rang'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>