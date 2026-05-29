<a href="index.php?element=emprunts&action=add" class="w3-margin w3-button w3-border w3-round">
    <b>Créer un nouvel emprunt</b>
</a>

<div class="w3-container w3-margin-bottom">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarEmprunt"
        onkeyup="filtrerEmprunts()"
        placeholder="Rechercher un nom, un matériel, une date... (pour plusieurs filtres, mettre une virgule (,) ou un point-virgule (;) entre les mots-clés)">
</div>

<table class="w3-table w3-striped w3-bordered w3-small w3-border">
    <thead>
        <tr class="w3-blue">
            <th>Nom <span class="sort-arrow"></span></th>
            <th>Prénom <span class="sort-arrow"></span></th>
            <th>Année <span class="sort-arrow"></span></th>
            <th>Date d'emprunt <span class="sort-arrow"></span></th>
            <th>Caution <span class="sort-arrow"></span></th>
            <th>Matériel <span class="sort-arrow"></span></th>
            <th>Date prévue de restitution <span class="sort-arrow"></span></th>
            <th>Date réelle de restitution <span class="sort-arrow"></span></th>
            <th>État <span class="sort-arrow"></span></th>
            <th>Remarque <span class="sort-arrow"></span></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($emprunts): ?>
            <?php foreach ($emprunts as $emprunt): ?>
                <?php
                $materielsLot = $emprunt->fetchMateriels();
                $today = new DateTime();
                $date_prevue = new DateTime($emprunt->date_prevue_restitution);
                $date_reelle = $emprunt->date_reelle_restitution ? new DateTime($emprunt->date_reelle_restitution) : null;
                $is_late = ($today > $date_prevue && !$date_reelle) || ($date_reelle && $date_reelle > $date_prevue);
                $row_style = $date_reelle ? 'opacity: 0.5;' : '';
                $afficherCompteurLot = ((int) $emprunt->nombre_materiels) > 1;

                $etat = $emprunt->date_reelle_restitution ? $emprunt->etat_restitution : 'En cours';
                $etat_class = '';

                if ($etat === 'OK') {
                    $etat_class = 'w3-text-green';
                } elseif ($etat === 'Endommagé') {
                    $etat_class = 'w3-text-red';
                } elseif ($etat === 'En réparation') {
                    $etat_class = 'w3-text-orange';
                } elseif ($etat === 'En cours') {
                    $etat_class = 'w3-text-blue';
                }
                ?>

                <tr class="item-emprunt" style="<?= $row_style ?>">
                    <td><?= sanitize($emprunt->nom_emprunteur) ?></td>
                    <td><?= sanitize($emprunt->prenom_emprunteur) ?></td>
                    <td><?= sanitize($emprunt->nom_groupe) ?></td>
                    <td><?= formatDisplayDate(sanitize($emprunt->date_emprunt)) ?></td>
                    <td>
                        <?php if ($emprunt->caution === 'Déposée'): ?>
                            <span style="color: green; font-weight: bold;">✓</span>
                        <?php elseif ($emprunt->caution === 'En attente'): ?>
                            <span style="color: red; font-weight: bold;">✕</span>
                        <?php else: ?>
                            <span style="font-weight: bold;">✕</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($afficherCompteurLot): ?>
                            <div>
                                <b><?= (int) $emprunt->nombre_materiels_rendus ?> / <?= (int) $emprunt->nombre_materiels ?></b>
                            </div>
                        <?php endif; ?>
                        <div><?= sanitize($emprunt->materiels_resume) ?></div>
                    </td>
                    <td><?= formatDisplayDate(sanitize($emprunt->date_prevue_restitution)) ?></td>
                    <td style="<?= $is_late ? 'color: red; font-weight: bold;' : '' ?>">
                        <?= formatDisplayDate(sanitize($emprunt->date_reelle_restitution)) ?>
                    </td>
                    <td class="<?= $etat_class ?>" style="font-weight: bold;">
                        <?= sanitize($etat) ?>
                    </td>
                    <td>
                        <?php if ($emprunt->date_reelle_restitution): ?>
                            <?= html_entity_decode(($emprunt->remarque_restitution ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?php else: ?>
                            <?= html_entity_decode(($emprunt->remarque ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="?element=emprunts&action=card" method="post">
                            <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                            <button type="submit" name="submit" class="w3-button w3-small w3-border w3-round">✏️</button>
                        </form>

                        <?php if (!$emprunt->date_reelle_restitution): ?>
                            <button command="show-modal" commandfor="confirmation-emprunt-dialog-<?= $emprunt->id_emprunt ?>" class="w3-button w3-small w3-border w3-round">📥</button>
                        <?php endif; ?>

                        <dialog class="w3-round-large" id="confirmation-emprunt-dialog-<?= $emprunt->id_emprunt ?>">
                            <div style="min-width: 700px; max-width: 900px;">
                                <h3>Rendu du lot</h3>
                                <p>
                                    Emprunt de <b><?= sanitize($emprunt->nom_emprunteur) ?> <?= sanitize($emprunt->prenom_emprunteur) ?></b>
                                    pour <b><?= sanitize($emprunt->nom_groupe) ?></b>.
                                </p>
                                <p>
                                    <?php if ($afficherCompteurLot): ?>
                                        <b><?= (int) $emprunt->nombre_materiels_rendus ?> / <?= (int) $emprunt->nombre_materiels ?></b> matériel(s) rendu(s)
                                    <?php else: ?>
                                        <b>Lot unique</b>
                                    <?php endif; ?>
                                </p>

                                <table class="w3-table w3-bordered w3-small w3-margin-bottom">
                                    <thead>
                                        <tr class="w3-blue">
                                            <th>Matériel</th>
                                            <th>État actuel</th>
                                            <th>Rendu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($materielsLot): ?>
                                            <?php foreach ($materielsLot as $materiel): ?>
                                                <tr>
                                                    <td>
                                                        <b><?= sanitize($materiel['nom_materiel']) ?></b><br>
                                                        <?= sanitize($materiel['modele_materiel']) ?><br>
                                                        <?= sanitize($materiel['etiquette_ulco_materiel']) ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($materiel['date_reelle_restitution'])): ?>
                                                            <span class="w3-text-green"><b>Déjà rendu</b></span><br>
                                                            <?= sanitize($materiel['etat_restitution']) ?>
                                                        <?php else: ?>
                                                            <span class="w3-text-blue">En cours</span><br>
                                                            <?php
                                                            if ($materiel['etat_materiel'] === 'OK') {
                                                                echo '<span class="w3-text-green">OK</span>';
                                                            } elseif ($materiel['etat_materiel'] === 'Endommagé') {
                                                                echo '<span class="w3-text-red">Endommagé</span>';
                                                            } elseif ($materiel['etat_materiel'] === 'En réparation') {
                                                                echo '<span class="w3-text-orange">En réparation</span>';
                                                            } elseif ($materiel['etat_materiel'] === 'En cours') {
                                                                echo '<span class="w3-text-blue">En cours</span>';
                                                            }
                                                            ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (empty($materiel['date_reelle_restitution'])): ?>
                                                            <form action="?element=emprunts&action=card" method="post" class="w3-container w3-padding-0">
                                                                <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                                                                <input type="hidden" name="id_materiel" value="<?= $materiel['id_materiel'] ?>">
                                                                <select class="w3-select w3-border w3-round w3-margin-bottom" name="etat_restitution" required>
                                                                    <option value="" disabled selected>État</option>
                                                                    <?php foreach (Materiel::$etats as $etatMateriel): ?>
                                                                        <option value="<?= sanitize($etatMateriel) ?>"><?= sanitize($etatMateriel) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" name="remarque_restitution" placeholder="Remarque de restitution">
                                                                <button type="submit" name="return_material" class="w3-button w3-small w3-blue w3-round">Rendre</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="w3-text-green">Terminé</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3">Aucun matériel associé à cet emprunt.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                                <div class="dialog-buttons">
                                    <button commandfor="confirmation-emprunt-dialog-<?= $emprunt->id_emprunt ?>" command="close" class="w3-button w3-small w3-border w3-round">Fermer</button>
                                </div>
                            </div>
                        </dialog>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11">Aucun emprunt trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<?php if ($emprunts): ?>
    <h2><?= count($emprunts) ?> emprunt(s) trouvé(s)</h2>
<?php endif ?>

<script>
    function filtrerEmprunts() {
        let input = document.getElementById('searchBarEmprunt').value.toLowerCase();
        let rows = document.getElementsByClassName('item-emprunt');
        let motsCles = input.split(/[,;]/).map(mot => mot.trim()).filter(Boolean);

        for (let i = 0; i < rows.length; i++) {
            let cellules = Array.from(rows[i].children);

            // on ignore la dernière cellule (la colonne Actions)
            let texteUtileLigne = cellules
                .slice(0, -1)
                .map(td => td.textContent || td.innerText)
                .join(" ")
                .toLowerCase();

            if (motsCles.length === 0) {
                rows[i].style.display = "";
                continue;
            }

            // On vérifie si les mots clés sont dans le texte des colonnes utiles
            let correspondAtousLesMots = motsCles.every(mot => texteUtileLigne.indexOf(mot) > -1);

            if (correspondAtousLesMots) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
</script>