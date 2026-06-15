<div class="w3-container w3-margin-top w3-padding-large">
    <div class="w3-center w3-margin-bottom">
        <h2><i class="w3-margin-right"></i><b>Statistiques d'Emprunts de Matériel</b></h2>
        <p class="w3-text-grey">Évolution du volume de prêts par groupe et année universitaire</p>
    </div>

    <div class="w3-container w3-margin-bottom">
        <input class="w3-input w3-border w3-round-xxlarge w3-center"
            type="search"
            id="searchBarStats"
            placeholder="Rechercher un matériel... (ex: PC, Raspberry, Câble)">
    </div>

    <div id="aucunDonneePanel" class="w3-panel w3-pale-blue w3-border w3-round w3-padding-large w3-center" style="display: <?= empty($fluxParAnnee) ? 'block' : 'none' ?>;">
        <h3>Aucune donnée disponible</h3>
        <p>Il n'y a pas d'historique de prêts enregistré pour ce matériel.</p>
    </div>

    <div id="statsGlobalContainer" style="display: <?= empty($fluxParAnnee) ? 'none' : 'block' ?>;">
        <div class="w3-card-4 w3-margin-bottom w3-round card-stat">
            <div class="w3-container w3-padding-16" style="overflow-x: auto;">
                <table class="w3-table-all w3-centered w3-card" id="tableStats">
                    <thead>
                        <tr class="w3-blue bg-gradient-teal w3-text-white" id="tableHeaderStats">
                            <th style="text-align: left;" class="w3-padding-large"><i class="fa fa-users w3-margin-right"></i>Groupe / Promotion</th>
                            <?php foreach ($listeAnnees as $annee): ?>
                                <th class="w3-padding-large"><?= htmlspecialchars($annee) ?></th>
                            <?php endforeach; ?>
                            <th class="w3-light-grey w3-text-black class-total-col">Total Groupe</th>
                        </tr>
                    </thead>
                    <tbody id="tableBodyStats">
                        <?php if (!empty($fluxParAnnee)): ?>
                            <?php foreach ($matriceData as $nomGroupe => $anneesAssociees):
                                $totalLigneGroupe = 0;
                            ?>
                                <tr>
                                    <td style="text-align: left;" class="w3-padding-large"><b><?= htmlspecialchars($nomGroupe) ?></b></td>
                                    <?php foreach ($listeAnnees as $annee):
                                        $nbPrets = isset($anneesAssociees[$annee]) ? $anneesAssociees[$annee] : 0;
                                        $totalLigneGroupe += $nbPrets;
                                    ?>
                                        <td class="w3-large"><?= $nbPrets ?></td>
                                    <?php endforeach; ?>
                                    <td class="w3-large w3-light-grey"><b><?= $totalLigneGroupe ?></b></td>
                                </tr>
                            <?php endforeach; ?>

                            <tr class="row-total" id="rowTotalStats">
                                <td style="text-align: left;" class="w3-padding-large">
                                    <b><i class="fa fa-calculator w3-margin-right"></i>TOTAL PAR ANNÉE</b>
                                </td>
                                <?php foreach ($listeAnnees as $annee): ?>
                                    <td class="w3-large"><b><?= $totauxParAnnee[$annee] ?></b></td>
                                <?php endforeach; ?>
                                <td class="w3-xlarge w3-text-teal"><b><?= $totalGeneral ?></b></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="w3-card-4 w3-round" style="max-width: 900px; margin: 20px auto;">
            <canvas id="chart"></canvas>
        </div>
    </div>
</div>

<script>
    // les différentes couleurs pour les courbes (s'il n'y a pas assez de couleurs, elles seront réutilisées en boucle)
    const paletteCouleurs = ['#ffeb3b', '#f44336', '#03a9f4', '#4caf50', '#ff9800', '#009688'];

    // initialisation de Chart.js avec les données de PHP
    const ctx = document.getElementById('chart');
    let myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($listeAnnees) ?>,
            datasets: [
                <?php
                $couleurIdx = 0;
                foreach ($matriceData as $nomGroupe => $anneesAssociees):
                    $dataPoints = [];
                    foreach ($listeAnnees as $annee) {
                        $dataPoints[] = isset($anneesAssociees[$annee]) ? $anneesAssociees[$annee] : 0;
                    }
                ?> {
                        label: <?= json_encode($nomGroupe) ?>,
                        data: <?= json_encode($dataPoints) ?>,
                        borderColor: paletteCouleurs[<?= $couleurIdx ?> % paletteCouleurs.length],
                        tension: 0,
                        fill: false
                    },
                <?php $couleurIdx++;
                endforeach; ?> {
                    label: 'Total',
                    data: <?php
                            $dataPointsTotaux = [];
                            foreach ($listeAnnees as $annee) {
                                $dataPointsTotaux[] = $totauxParAnnee[$annee];
                            }
                            echo json_encode($dataPointsTotaux);
                            ?>,
                    borderColor: '#9c27b0',
                    tension: 0,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    offset: true
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    function rechargerTableauEtGraphique(donnees) {
        const panelAucun = document.getElementById('aucunDonneePanel');
        const containerGlobal = document.getElementById('statsGlobalContainer');

        if (!donnees || donnees.length === 0) {
            panelAucun.style.display = "block";
            containerGlobal.style.display = "none";
            return;
        }

        panelAucun.style.display = "none";
        containerGlobal.style.display = "block";

        // on extrait les structures d'années et de groupes
        const annees = [...new Set(donnees.map(d => d.annee_universitaire))].sort();
        const groupes = [...new Set(donnees.map(d => d.nom_groupe))].sort();

        // on reconstruit l'entête du tableau
        const headerRow = document.getElementById('tableHeaderStats');
        let headerHtml = `<th style="text-align: left;" class="w3-padding-large"><i class="fa fa-users w3-margin-right"></i>Groupe / Promotion</th>`;
        annees.forEach(annee => {
            headerHtml += `<th class="w3-padding-large">${annee}</th>`;
        });
        headerHtml += `<th class="w3-light-grey w3-text-black class-total-col">Total Groupe</th>`;
        headerRow.innerHTML = headerHtml;

        // on organise les données pour la matrice
        let matrice = {};
        let totauxParAnnee = {};
        annees.forEach(a => totauxParAnnee[a] = 0);
        let totalGeneral = 0;

        groupes.forEach(g => {
            matrice[g] = {};
            annees.forEach(a => matrice[g][a] = 0);
        });

        donnees.forEach(d => {
            const nb = parseInt(d.nombre_prets);
            matrice[d.nom_groupe][d.annee_universitaire] = nb;
            totauxParAnnee[d.annee_universitaire] += nb;
            totalGeneral += nb;
        });

        // on génére le corps du tableau
        let bodyHtml = "";
        let datasetsGraphique = [];
        let couleurIndex = 0;

        for (const [nomGroupe, anneesAssociees] of Object.entries(matrice)) {
            let totalLigneGroupe = 0;
            let dataPointsGraphique = [];

            bodyHtml += `<tr><td style="text-align: left;" class="w3-padding-large"><b>${nomGroupe}</b></td>`;
            annees.forEach(annee => {
                const nb = anneesAssociees[annee];
                totalLigneGroupe += nb;
                dataPointsGraphique.push(nb);
                bodyHtml += `<td class="w3-large">${nb}</td>`;
            });
            bodyHtml += `<td class="w3-large w3-light-grey"><b>${totalLigneGroupe}</b></td></tr>`;

            // on l'ajoute au dataset Chart.js
            datasetsGraphique.push({
                label: nomGroupe,
                data: dataPointsGraphique,
                borderColor: paletteCouleurs[couleurIndex % paletteCouleurs.length],
                tension: 0,
                fill: false
            });
            couleurIndex++;
        }

        // ligne des totaux généraux de fin
        bodyHtml += `<tr class="w3-pale-green row-total" style="border-top: 3px solid #009688;">
            <td style="text-align: left;" class="w3-padding-large"><b><i class="fa fa-calculator w3-margin-right"></i>TOTAL PAR ANNÉE</b></td>`;

        let dataPointsTotaux = [];
        annees.forEach(annee => {
            bodyHtml += `<td class="w3-large"><b>${totauxParAnnee[annee]}</b></td>`;
            dataPointsTotaux.push(totauxParAnnee[annee]);
        });
        bodyHtml += `<td class="w3-xlarge w3-text-teal"><b>${totalGeneral}</b></td></tr>`;

        document.getElementById('tableBodyStats').innerHTML = bodyHtml;

        // on ajoute la courbe "Total" au graphique
        datasetsGraphique.push({
            label: 'Total',
            data: dataPointsTotaux,
            borderColor: '#9c27b0',
            tension: 0,
            fill: false
        });

        // on met à jour l'instance Chart.js
        myChart.data.labels = annees;
        myChart.data.datasets = datasetsGraphique;
        myChart.update();
    }

    // éviter de faire trop de requêtes lors de la saisie dans la barre de recherche
    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                func.apply(this, args);
            }, timeout);
        };
    }

    function chargerStats(recherche) {
        const basePath = '<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>';
        const url = `${basePath}/api/get_stats.php?search=${encodeURIComponent(recherche)}`;
        fetch(url)
            .then(res => res.json())
            .then(donnees => {
                rechargerTableauEtGraphique(donnees);
            })
            .catch(err => console.error("Erreur de synchronisation :", err));
    }

    // on "écoute" les changements dans la barre de recherche pour faire la requête
    document.getElementById('searchBarStats').addEventListener('input', debounce((e) => {
        chargerStats(e.target.value.trim());
    }));

    // par défaut, on affiche les stats pour les "PC" à l'ouverture de la page
    document.getElementById('searchBarStats').value = 'PC';
    chargerStats('PC');
</script>