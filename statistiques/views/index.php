<div class="w3-container w3-margin-top w3-padding-large">
    <div class="w3-center w3-margin-bottom">
        <h2><i class="w3-margin-right"></i><b>Statistiques d'Emprunts de Matériel</b></h2>
        <p class="w3-text-grey">Évolution du volume de prêts d'ordinateurs par groupe et année universitaire</p>
    </div>

    <?php
    if (empty($fluxParAnnee)):
    ?>
        <div class="w3-panel w3-pale-blue w3-border w3-round w3-padding-large w3-center">
            <h3>Aucune donnée disponible</h3>
            <p>Il n'y a pas encore d'historique de prêts d'ordinateurs enregistré en base de données.</p>
        </div>
    <?php
    else:
    ?>

        <div class="w3-card-4 w3-margin-bottom w3-round card-stat">
            <div class="w3-container w3-padding-16" style="overflow-x: auto;">
                <table class="w3-table-all w3-centered w3-card">
                    <thead>
                        <tr class="w3-blue bg-gradient-teal w3-text-white">
                            <th style="text-align: left;" class="w3-padding-large"><i class="fa fa-users w3-margin-right"></i>Groupe / Promotion</th>

                            <?php foreach ($listeAnnees as $annee): ?>
                                <th class="w3-padding-large"><i class="w3-margin-right"></i><?= htmlspecialchars($annee) ?></th>
                            <?php endforeach; ?>

                            <th class="w3-light-grey w3-text-black class-total-col">Total Groupe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matriceData as $nomGroupe => $anneesAssociees):
                            $totalLigneGroupe = 0;
                        ?>
                            <tr>
                                <td style="text-align: left;" class="w3-padding-large">
                                    <b><?= htmlspecialchars($nomGroupe) ?></b>
                                </td>

                                <?php foreach ($listeAnnees as $annee):
                                    $nbPrets = isset($anneesAssociees[$annee]) ? $anneesAssociees[$annee] : 0;
                                    $totalLigneGroupe += $nbPrets;
                                ?>
                                    <td class="w3-large">
                                        <?php if ($nbPrets > 0): ?>
                                            <?= $nbPrets ?>
                                        <?php else: ?>
                                            <span>0</span> 
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <td class="w3-large w3-light-grey"><b><?= $totalLigneGroupe ?></b></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="w3-pale-green row-total" style="border-top: 3px solid #009688;">
                            <td style="text-align: left;" class="w3-padding-large">
                                <b><i class="fa fa-calculator w3-margin-right"></i>TOTAL PAR ANNÉE</b>
                            </td>

                            <?php foreach ($listeAnnees as $annee): ?>
                                <td class="w3-large"><b><?= $totauxParAnnee[$annee] ?></b></td>
                            <?php endforeach; ?>

                            <td class="w3-xlarge w3-text-teal"><b><?= $totalGeneral ?></b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="w3-card-4 w3-round" style="max-width: 900px; margin: 20px auto;">
            <canvas id="myChart"></canvas>
        </div>

    <?php endif; ?>
</div>

<script>
    // on récupère la liste des années générée par PHP
    const labels = <?= json_encode($listeAnnees) ?>;

    // palette de couleurs pour les différents groupes
    const paletteCouleurs = ['#ffeb3b', '#f44336', '#03a9f4', '#4caf50', '#ff9800', '#009688'];
    let couleurIndex = 0;

    const datasetsGraphique = [];

    <?php 
    foreach ($matriceData as $nomGroupe => $anneesAssociees): 
        $dataPoints = [];
        foreach ($listeAnnees as $annee) {
            $dataPoints[] = isset($anneesAssociees[$annee]) ? $anneesAssociees[$annee] : 0;
        }
    ?>
        datasetsGraphique.push({
            label: <?= json_encode($nomGroupe) ?>,
            data: <?= json_encode($dataPoints) ?>,
            borderColor: paletteCouleurs[couleurIndex % paletteCouleurs.length],
            tension: 0,
            fill: false
        });
        couleurIndex++;
    <?php endforeach; ?>

    <?php 
        $dataPointsTotaux = [];
        foreach ($listeAnnees as $annee) {
            $dataPointsTotaux[] = $totauxParAnnee[$annee];
        }
    ?>
    datasetsGraphique.push({
        label: 'Total',
        data: <?= json_encode($dataPointsTotaux) ?>,
        borderColor: '#9c27b0',
        tension: 0,
        fill: false
    });

    // config globale Chart.js
    const config = {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasetsGraphique
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Évolution des Emprunts de Matériel'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
    };

    // initialisation du graphique
    const myChart = new Chart(
        document.getElementById('myChart'),
        config
    );
</script>