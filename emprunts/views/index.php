<a href="index.php?element=emprunts&action=add" class="w3-margin w3-button w3-border">
    <b>Créer un nouvel emprunt</b>
</a>

<div class="w3-container w3-margin-bottom">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarEmprunt"
        onkeyup="filtrerEmprunts()"
        placeholder="Rechercher un nom, un matériel, une date...">
</div>

<table class="w3-table w3-striped w3-bordered w3-small w3-border">
    <thead>
        <tr class="w3-light-gray">
            <th>Nom</th>
            <th>Prénom</th>
            <th>Année</th>
            <th>Date d'emprunt</th>
            <th>Caution</th>
            <th>Matériel</th>
            <th>Date prévue de restitution</th>
            <th>Date réelle de restitution</th>
            <th>État</th>
            <th>Remarque</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($emprunts): ?>
            <?php foreach ($emprunts as $emprunt): ?>
                <?php
                $today = new DateTime();
                $date_prevue = new DateTime($emprunt->date_prevue_restitution);
                $date_reelle = $emprunt->date_reelle_restitution ? new DateTime($emprunt->date_reelle_restitution) : null;
                $is_late = ($today > $date_prevue && !$date_reelle) || ($date_reelle && $date_reelle > $date_prevue);
                $row_style = $date_reelle ? 'opacity: 0.5;' : '';

                $etat_class = '';
                $etat = $emprunt->date_reelle_restitution ? $emprunt->etat_restitution : $emprunt->etat;

                if ($etat === 'OK') {
                    $etat_class = 'w3-text-green';
                } elseif ($etat === 'Endommagé') {
                    $etat_class = 'w3-text-red';
                } elseif ($etat === 'En réparation') {
                    $etat_class = 'w3-text-orange';
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
                        <form action="?element=materiels&action=card" method="post">
                            <input type="hidden" name="id_materiel" value="<?= $emprunt->id_materiel ?>">
                            <input type="hidden" name="old_page" value="emprunts">
                            <button type="submit" class="w3-button w3-small w3-border w3-round"><?= sanitize($emprunt->nom_materiel) ?></button>
                        </form>
                    </td>
                    <td style="display:none;">
                        <?= sanitize($emprunt->etiquette_ulco_materiel) ?>
                        <?= sanitize($emprunt->modele_materiel) ?>
                    </td>
                    <td><?= formatDisplayDate(sanitize($emprunt->date_prevue_restitution)) ?></td>
                    <td style="<?= $is_late ? 'color: red; font-weight: bold;' : '' ?>">
                        <?= formatDisplayDate(sanitize($emprunt->date_reelle_restitution)) ?>
                    </td>
                    <td class="<?= $etat_class ?>" style="font-weight: bold;">
                        <?php if ($emprunt->date_reelle_restitution): ?>
                            <?= sanitize($emprunt->etat_restitution) ?>
                        <?php else: ?>
                            <?= sanitize($emprunt->etat) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($emprunt->date_reelle_restitution): ?>
                            <?= sanitize($emprunt->remarque_restitution) ?>
                        <?php else: ?>
                            <?= sanitize($emprunt->remarque) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="?element=emprunts&action=card" method="post">
                            <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                            <button type="submit" name="submit" class="w3-button w3-small w3-border w3-round">✏️</button>
                        </form>

                        <dialog class="w3-round-large" id="confirmation-emprunt-dialog">
                            <p>
                                L'état du matériel actuel est "<span class="<?= $etat_class ?>"><?= sanitize($emprunt->etat) ?></span>" </br> Êtes-vous sûr de valider maintenant ?
                            </p>

                            <div class="dialog-buttons">
                                <button commandfor="confirmation-emprunt-dialog" command="close" class="w3-button w3-small w3-border w3-round">Annuler</button>

                                <form action="?element=materiels&action=card" method="post">
                                    <input type="hidden" name="id_materiel" value="<?= $emprunt->id_materiel ?>">
                                    <button type="submit" class="w3-button w3-small w3-border w3-round">Changer l'état</button>
                                </form>

                                <form action="?element=emprunts" method="post">
                                    <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                                    <button type="submit" name="submit_date" class="w3-button w3-small w3-border w3-round">Valider</button>
                                </form>
                            </div>
                        </dialog>

                        <?php if (!$emprunt->date_reelle_restitution): ?>
                            <button command="show-modal" commandfor="confirmation-emprunt-dialog" name="submit_date" class="w3-button w3-small w3-border w3-round">📥</button>
                        <?php endif; ?>
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

<script>
    function filtrerEmprunts() {
        let input = document.getElementById('searchBarEmprunt').value.toLowerCase();
        let rows = document.getElementsByClassName('item-emprunt');

        for (let i = 0; i < rows.length; i++) {
            let texteLigne = rows[i].textContent || rows[i].innerText;

            // 3. Comparaison
            if (texteLigne.toLowerCase().indexOf(input) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
</script>

<style>
    .dialog-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }

    .dialog-buttons form {
        margin: 0;
        display: inline-block;
    }

    #confirmation-emprunt-dialog:modal {
        border: 2px solid #0093d2;
        padding: 25px;
    }
</style>