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
            <label><b>Remarque</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="remarque"
                placeholder="C'est certainement une superbe remarque" rows="1"><?= html_entity_decode(($emprunt->remarque ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Mettre à jour -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-blue w3-round" type="submit"
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

<div class="w3-container w3-margin-top">
    <h3><b>Matériels du lot</b></h3>
    <p><?= (int) $emprunt->nombre_materiels_rendus ?> / <?= (int) $emprunt->nombre_materiels ?> matériel(s) rendu(s)</p>

    <table class="w3-table w3-striped w3-bordered w3-small w3-border">
        <thead>
            <tr class="w3-blue">
                <th>Nom</th>
                <th>Modèle</th>
                <th>identifiant</th>
                <th>Statut</th>
                <th>Date de rendu</th>
                <th>État de restitution</th>
                <th>Remarque</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($materiels): ?>
                <?php foreach ($materiels as $materiel): ?>
                    <tr>
                        <td><?= sanitize($materiel['nom_materiel']) ?></td>
                        <td><?= sanitize($materiel['modele_materiel']) ?></td>
                        <td><?= sanitize($materiel['etiquette_ulco_materiel']) ?></td>
                        <td>
                            <?php if (!empty($materiel['date_reelle_restitution'])): ?>
                                <span class="w3-text-green"><b>Rendu</b></span>
                            <?php else: ?>
                                <span class="w3-text-blue"><b>En cours</b></span>
                            <?php endif; ?>
                        </td>
                        <?php
                            if (!empty($materiel['date_reelle_restitution']) && $materiel['date_reelle_restitution'] > $emprunt->date_prevue_restitution) {
                                echo '<td class="w3-text-orange">' . formatDisplayDate(sanitize($materiel['date_reelle_restitution'])) . '</td>';
                            } elseif (!empty($materiel['date_reelle_restitution'])) {
                                echo '<td class="w3-text-green">' . formatDisplayDate(sanitize($materiel['date_reelle_restitution'])) . '</td>';
                            } else {
                                echo '<td class="w3-text-red">Non rendu</td>';
                            }
                        ?>
                        <td><?= sanitize($materiel['etat_restitution']) ?></td>
                        <td><?= html_entity_decode(($materiel['remarque_restitution'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if (empty($materiel['date_reelle_restitution'])): ?>
                                <form action="?element=emprunts&action=card" method="post" class="w3-container w3-padding-0">
                                    <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                                    <input type="hidden" name="id_materiel" value="<?= $materiel['id_materiel'] ?>">
                                    <select class="w3-select w3-border w3-round w3-margin-bottom" name="etat_restitution" required>
                                        <option value="" disabled selected>État</option>
                                        <?php foreach (Materiel::$etats as $etat): ?>
                                            <option value="<?= sanitize($etat) ?>"><?= sanitize($etat) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" name="remarque_restitution" placeholder="Remarque de restitution">
                                    <button type="submit" name="return_material" class="w3-button w3-small w3-blue w3-round">Rendre</button>
                                </form>
                            <?php else: ?>
                                <span class="w3-text-green">Déjà rendu</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="w3-center">Aucun matériel associé à cet emprunt.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>