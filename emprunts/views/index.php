<a href="index.php?element=emprunts&action=add" class="w3-margin w3-button w3-border">
    <b>Créer un nouvel emprunt</b>
</a>

<div class="w3-container w3-margin-bottom">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarEmprunt"
        onkeyup="filtrerEmprunts()"
        placeholder="Rechercher un nom, un matériel, une date, une date...">
</div>

<table class="w3-table w3-striped w3-bordered w3-small">
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
                if ($emprunt->etat === 'OK') {
                    $etat_class = 'w3-text-green';
                } elseif ($emprunt->etat === 'Endommagé') {
                    $etat_class = 'w3-text-red';
                } elseif ($emprunt->etat === 'En réparation') {
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
                        <a href="?page=materiels&action=card&id=<?= $emprunt->id_materiel ?>"
                            style="color: #0093d2;">
                            <?= sanitize($emprunt->nom_materiel) ?>
                        </a>
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
                        <?= sanitize($emprunt->etat) ?>
                    </td>
                    <td><?= sanitize($emprunt->remarque) ?></td>
                    <td>
                        <form action="?element=emprunts&action=card" method="post">
                            <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                            <button type="submit" name="submit" class="w3-button w3-small w3-border w3-round">✏️</button>
                        </form>

                        <?php if (!$emprunt->date_reelle_restitution): ?>
                            <form action="?element=emprunts" method="post">
                                <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                                <button type="submit" name="submit_date" class="w3-button w3-small w3-border w3-round">📥</button>
                            </form>
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