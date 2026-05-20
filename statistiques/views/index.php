<div class="w3-container w3-margin-top w3-padding-large">
    <div class="w3-center w3-margin-bottom">
        <h2><i class="fa fa-bar-chart w3-margin-right"></i><b>Statistiques d'Emprunts de Matériel</b></h2>
        <p class="w3-text-grey">Volume de prêts d'ordinateurs par année universitaire et par groupe</p>
    </div>

    <?php if (empty($fluxParAnnee)): ?>
        <div class="w3-panel w3-pale-blue w3-border w3-round w3-padding-large w3-center">
            <h3>Aucune donnée disponible</h3>
            <p>Il n'y a pas encore d'historique de prêts d'ordinateurs enregistré en base de données.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($fluxParAnnee as $annee => $groupes): ?>
        <div class="w3-card-4 w3-white w3-margin-bottom w3-round card-stat">
            <header class="w3-container w3-teal w3-padding bg-gradient-teal">
                <h3 class="w3-margin-none">
                    <i class="fa fa-calendar w3-margin-right"></i>Année Universitaire : <b><?= $annee ?></b>
                </h3>
            </header>
            
            <div class="w3-container w3-padding-16">
                <table class="w3-table-all w3-hoverable w3-centered">
                    <thead>
                        <tr class="w3-light-grey">
                            <th style="width: 60%; text-align: left;" class="w3-padding-large">Groupe / Promotion</th>
                            <th style="width: 40%;" class="w3-padding-large">Nombre d'ordinateurs prêtés</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalAnnee = 0;
                        foreach ($groupes as $g): 
                            $totalAnnee += $g['nombre_prets'];
                        ?>
                            <tr>
                                <td style="text-align: left;" class="w3-padding-large">
                                    <i class="fa fa-users w3-text-grey w3-margin-right"></i>
                                    <b><?= htmlspecialchars($g['nom_groupe']) ?></b>
                                </td>
                                <td class="w3-large w3-padding-large"><?= $g['nombre_prets'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <tr class="w3-pale-green row-total">
                            <td style="text-align: left;" class="w3-padding-large">
                                <b><i class="fa fa-calculator w3-margin-right"></i>TOTAL GLOBAL DES PRÊTS</b>
                            </td>
                            <td class="w3-xlarge w3-padding-large">
                                <b><?= $totalAnnee ?></b> <span class="w3-medium w3-text-grey">machine<?= $totalAnnee > 1 ? 's' : '' ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>