<a href="index.php?element=emprunts&action=add" class="w3-margin w3-button w3-border w3-round">
    <b>Créer un nouvel emprunt</b>
</a>

<div class="w3-container w3-margin-bottom w3-margin-top">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarEmprunt"
        onkeyup="filtrerEmprunts()"
        placeholder="Rechercher par nom, par matériel, par date... (plusieurs filtres: virgule ou point-virgule)">
</div>

<?php $totalEmprunts = $emprunts ? count($emprunts) : 0; ?>

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

                $nbTotal = (int) $emprunt->nombre_materiels;
                $nbRendus = (int) $emprunt->nombre_materiels_rendus;
                $toutEstRendu = ($nbTotal > 0 && $nbRendus === $nbTotal);

                $is_late = ($today > $date_prevue && !$date_reelle) || ($date_reelle && $date_reelle > $date_prevue);

                $row_style = $date_reelle ? 'opacity: 0.5;' : '';
                $afficherCompteurLot = ((int) $emprunt->nombre_materiels) > 1;

                if ($toutEstRendu) {
                    $etat = $emprunt->etat_restitution ? $emprunt->etat_restitution : 'OK';
                } else {
                    $etat = ($nbRendus > 0) ? "En cours ($nbRendus/$nbTotal rendus)" : 'En cours';
                }

                $etat_class = '';
                if ($toutEstRendu && $etat === 'OK') $etat_class = 'w3-text-green';
                elseif ($toutEstRendu && $etat === 'Endommagé') $etat_class = 'w3-text-red';
                elseif ($toutEstRendu && $etat === 'En réparation') $etat_class = 'w3-text-orange';
                else $etat_class = 'w3-text-blue';
                ?>

                <tr class="item-emprunt" style="<?= $row_style ?>" data-materiels-details="<?= sanitize($emprunt->materiels_details) ?>" data-rendu="<?= ($toutEstRendu || !empty($emprunt->date_reelle_restitution)) ? '1' : '0' ?>">
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
                        <?php
                        $materielsAssoc = $emprunt->getMaterielsAssoc();
                        $nbMaterielsAssoc = count($materielsAssoc);
                        if ($nbMaterielsAssoc === 1):
                            $id_single_materiel = key($materielsAssoc);
                        ?>
                            <a href="index.php?element=materiels&action=card&id_materiel=<?= $id_single_materiel ?>&old_page=emprunts" class="w3-button w3-small w3-border w3-round w3-block w3-left-align" style="text-decoration: none;">
                                <span style="text-align: center; display: block;"><?= sanitize($emprunt->materiels_resume) ?></span>
                            </a>
                        <?php elseif ($nbMaterielsAssoc > 1): ?>
                            <button type="button" command="show-modal" commandfor="select-materiel-dialog-<?= $emprunt->id_emprunt ?>" class="w3-button w3-small w3-border w3-round w3-block w3-left-align">
                                <?php if ($afficherCompteurLot): ?>
                                    <span style="display: block; padding-left: 8px; padding-right: 8px; text-align: center;" class="w3-blue w3-round">
                                        <b><?= $nbRendus ?> / <?= $nbTotal ?></b>
                                    </span>
                                <?php endif; ?>
                                <span style="text-align: center; display: block;"><?= sanitize($emprunt->materiels_resume) ?></span>
                            </button>

                            <dialog class="w3-round-large" id="select-materiel-dialog-<?= $emprunt->id_emprunt ?>">
                                <div style="min-width: 400px; max-width: 600px; padding: 15px; text-align: left;">
                                    <h3 class="w3-text-blue"><b>Choisir un matériel</b></h3>

                                    <p>Cet emprunt contient plusieurs matériels. Sélectionnez le matériel à visualiser :</p>

                                    <div class="w3-margin-top w3-margin-bottom" style="display: flex; flex-direction: column; gap: 8px;">
                                        <?php foreach ($materielsAssoc as $id_mat => $nom_mat): ?>
                                            <a href="index.php?element=materiels&action=card&id_materiel=<?= $id_mat ?>&old_page=emprunts" class="w3-button w3-border w3-round w3-light-grey w3-block w3-left-align" style="text-decoration: none;">
                                                <?= sanitize($nom_mat) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="dialog-buttons w3-right-align w3-margin-top">
                                        <button commandfor="select-materiel-dialog-<?= $emprunt->id_emprunt ?>" command="close" class="w3-button w3-small w3-border w3-round">Fermer</button>
                                    </div>
                                </div>
                            </dialog>
                        <?php else: ?>
                            <span style="text-align: center; display: block;"><?= sanitize($emprunt->materiels_resume) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= formatDisplayDate(sanitize($emprunt->date_prevue_restitution)) ?></td>
                    <td style="<?= $is_late ? 'color: red; font-weight: bold;' : '' ?>">
                        <?= formatDisplayDate(sanitize($emprunt->date_reelle_restitution)) ?>
                    </td>
                    <td class="<?= $etat_class ?>" style="font-weight: bold;">
                        <?= sanitize($etat) ?>
                    </td>
                    <td>
                        <?php if ($toutEstRendu): ?>
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

                        <?php if (!$toutEstRendu): ?>
                            <button command="show-modal" commandfor="confirmation-emprunt-dialog-<?= $emprunt->id_emprunt ?>" class="w3-button w3-small w3-border w3-round" title="Gérer les rendus du lot">📥</button>
                        <?php endif; ?>

                        <dialog class="w3-round-large" id="confirmation-emprunt-dialog-<?= $emprunt->id_emprunt ?>">
                            <div style="min-width: 700px; max-width: 900px;">
                                <h3 class="w3-text-blue"><b>Suivi et retour du lot d'emprunt</b></h3>
                                <p>Emprunt de <b><?= sanitize($emprunt->nom_emprunteur . ' ' . $emprunt->prenom_emprunteur) . ' - ' . $emprunt->nom_groupe ?></b></p>
                                <p>
                                    Statut des retours : <b id="compteur-modal-<?= $emprunt->id_emprunt ?>"><?= $nbRendus ?> / <?= $nbTotal ?></b> matériel(s) rendu(s).
                                </p>

                                <table class="w3-table w3-bordered w3-small w3-margin-bottom">
                                    <thead>
                                        <tr class="w3-blue">
                                            <th>Matériel</th>
                                            <th>État actuel</th>
                                            <th>Actions / Rendu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($materielsLot): ?>
                                            <?php foreach ($materielsLot as $materiel): ?>
                                                <tr>
                                                    <td>
                                                        <b><?= sanitize($materiel['nom_materiel']) ?></b><br>
                                                        <small class="w3-text-grey">
                                                            Modèle : <?= sanitize($materiel['modele_materiel'] ?? 'N/A') ?><br>
                                                            Identifiant : <?= sanitize($materiel['etiquette_ulco_materiel'] ?? 'N/A') ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($materiel['date_reelle_restitution'])): ?>
                                                            <span class="w3-text-green"><b>Déjà rendu</b></span><br>
                                                            <small class="w3-text-grey">Le <?= formatDisplayDate($materiel['date_reelle_restitution']) ?> (<?= sanitize($materiel['etat_restitution']) ?>)</small>
                                                        <?php else: ?>
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
                                                            <form action="?element=emprunts&action=card" method="post" class="w3-container w3-padding-0 form-rendre-ajax">
                                                                <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                                                                <input type="hidden" name="id_materiel" value="<?= $materiel['id_materiel'] ?>">

                                                                <select class="w3-select w3-border w3-round w3-margin-bottom" name="etat_restitution" onchange="changerCouleurSelect(this)" required>
                                                                    <?php foreach (Materiel::$etats as $etatMateriel):
                                                                        $classEtat = '';
                                                                        if ($etatMateriel === 'OK') $classEtat = 'etat-ok';
                                                                        elseif ($etatMateriel === 'Réservé') $classEtat = 'etat-reserve';
                                                                        elseif ($etatMateriel === 'En réparation') $classEtat = 'etat-reparation';
                                                                        elseif ($etatMateriel === 'Endommagé') $classEtat = 'etat-endommage';
                                                                        elseif ($etatMateriel === 'Disparu') $classEtat = 'etat-disparu';
                                                                    ?>
                                                                        <option class="<?= $classEtat ?>" value="<?= sanitize($etatMateriel) ?>" <?= $etatMateriel === 'OK' ? 'selected' : '' ?>>
                                                                            <?= sanitize($etatMateriel) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>

                                                                <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" name="remarque_restitution" placeholder="Remarque de restitution">
                                                                <input type="hidden" name="return_material" value="1">
                                                                <button type="submit" class="w3-button w3-small w3-blue w3-round">Valider le rendu</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="w3-text-green">✓ Terminé</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="w3-center">Aucun matériel associé à cet emprunt.</td>
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
                <td colspan="11" class="w3-center">Aucun emprunt trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<h2 id="compteurEmprunts"><?= $totalEmprunts ?> / <?= $totalEmprunts ?> emprunt(s) trouvé(s)</h2>

<script>
    function filtrerEmprunts() {
        let input = document.getElementById('searchBarEmprunt').value.toLowerCase();
        let rows = document.getElementsByClassName('item-emprunt');
        let compteurEmprunts = document.getElementById('compteurEmprunts');
        let actionColumns = 1;
        let motsCles = input.split(/[,;]/).map(mot => mot.trim()).filter(Boolean);
        let empruntsVisibles = 0;

        for (let i = 0; i < rows.length; i++) {
            let cellules = Array.from(rows[i].children);
            let cellulesUtiles = actionColumns > 0 ? cellules.slice(0, -actionColumns) : cellules;

            // on ignore la dernière cellule (la colonne Actions)
            let texteUtileLigne = cellulesUtiles
                .map(td => td.textContent || td.innerText)
                .join(" ")
                .toLowerCase();

            if (motsCles.length === 0) {
                rows[i].style.display = "";
                empruntsVisibles++;
                continue;
            }

            // On vérifie si les mots clés sont dans le texte des colonnes utiles
            let correspondAtousLesMots = motsCles.every(mot => texteUtileLigne.indexOf(mot) > -1);

            if (correspondAtousLesMots) {
                rows[i].style.display = "";
                empruntsVisibles++;
            } else {
                rows[i].style.display = "none";
            }
        }

        // Tri des lignes : les emprunts non rendus (data-rendu="0") d'abord, les rendus (data-rendu="1") à la fin.
        let tbody = document.querySelector('table.w3-table tbody');
        if (tbody) {
            let rowsArray = Array.from(rows);
            // Tri stable pour conserver l'ordre initial ou celui du tri par colonne
            let mapped = rowsArray.map((el, i) => ({ index: i, value: el }));
            mapped.sort((a, b) => {
                let aRendu = a.value.getAttribute('data-rendu') === '1' ? 1 : 0;
                let bRendu = b.value.getAttribute('data-rendu') === '1' ? 1 : 0;
                if (aRendu !== bRendu) {
                    return aRendu - bRendu; // 0 avant 1 (non rendu d'abord)
                }
                return a.index - b.index;
            });
            mapped.forEach(item => {
                tbody.appendChild(item.value);
            });
        }

        if (compteurEmprunts) {
            compteurEmprunts.textContent = empruntsVisibles + ' / <?= $totalEmprunts ?> emprunt(s) trouvé(s)';
        }
    }

    filtrerEmprunts();

    document.addEventListener("DOMContentLoaded", function() {
        let forms = document.querySelectorAll('.form-rendre-ajax');

        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                let formData = new FormData(form);
                let idEmprunt = formData.get('id_emprunt'); // on récupère l'ID de l'emprunt

                let tdAction = form.closest('td');
                let tdEtatActuel = tdAction.previousElementSibling;
                let selectEtat = form.querySelector('select[name="etat_restitution"]');
                let etatSelectionne = selectEtat.value;

                let dialog = document.getElementById('confirmation-emprunt-dialog-' + idEmprunt);
                let rowPrincipale = dialog ? dialog.closest('tr.item-emprunt') : null;

                fetch('emprunts/controllers/card.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Réponse serveur incorrecte");
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // mise à jour du Dialog
                            tdEtatActuel.innerHTML = '<span class="w3-text-green"><b>Déjà rendu</b></span><br>' + etatSelectionne;
                            tdAction.innerHTML = '<span class="w3-text-green">Terminé</span>';

                            // mise à jour de la ligne du tableau
                            if (rowPrincipale) {
                                let cellules = rowPrincipale.cells;

                                // colonne Matériel (Compteur + Résumé)
                                let divCompteur = cellules[5].querySelector('b');
                                if (divCompteur) {
                                    divCompteur.textContent = data.nombre_materiels_rendus + ' / ' + data.nombre_materiels;
                                }

                                let divCompteurModal = document.getElementById('compteur-modal-' + idEmprunt);
                                if (divCompteurModal) {
                                    divCompteurModal.textContent = data.nombre_materiels_rendus + ' / ' + data.nombre_materiels;
                                }

                                // colonne Date réelle de restitution
                                if (data.date_reelle_restitution) {
                                    cellules[7].textContent = data.date_reelle_restitution;
                                    cellules[7].style.color = "";
                                    cellules[7].style.fontWeight = "bold";
                                }

                                // colonne État global
                                let cellEtatGlobal = cellules[8];
                                cellEtatGlobal.textContent = data.etat_global;

                                // réapplication des classes w3.css
                                cellEtatGlobal.classList.remove('w3-text-blue', 'w3-text-green', 'w3-text-red', 'w3-text-orange');

                                if (data.etat_global === 'OK') {
                                    cellEtatGlobal.classList.add('w3-text-green');
                                    rowPrincipale.style.opacity = "0.5";
                                } else if (data.etat_global === 'Endommagé') {
                                    cellEtatGlobal.classList.add('w3-text-red');
                                    rowPrincipale.style.opacity = "0.5";
                                } else if (data.etat_global === 'En réparation') {
                                    cellEtatGlobal.classList.add('w3-text-orange');
                                    rowPrincipale.style.opacity = "0.5";
                                } else {
                                    cellEtatGlobal.classList.add('w3-text-blue');
                                    rowPrincipale.style.opacity = "1";
                                }

                                // colonne Remarque de restitution
                                if (data.remarque_restitution) {
                                    cellules[9].textContent = data.remarque_restitution;
                                }

                                let isRendu = (data.date_reelle_restitution && data.date_reelle_restitution !== 'En attente');
                                rowPrincipale.setAttribute('data-rendu', isRendu ? '1' : '0');
                                filtrerEmprunts();
                            }

                        } else {
                            alert("Erreur: " + (data.error || "Inconnue"));
                        }
                    })
                    .catch(error => {
                        console.error("Erreur AJAX :", error);
                        alert("Impossible de joindre le serveur ou erreur de traitement.");
                    });
            });
        });
    });
</script>