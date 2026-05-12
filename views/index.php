<div class="w3-row-padding w3-margin-top">
    <div class="w3-third">
        <div class="w3-container w3-red w3-padding-16 w3-round">
            <div class="w3-left"><i class="fa fa-exclamation-triangle w3-xxxlarge"></i></div>
            <div class="w3-right"><h3><?=sanitize($retards) ?></h3></div>
            <div class="w3-clear"></div>
            <h4>Retards</h4>
        </div>
    </div>
    
    <div class="w3-third">
        <div class="w3-container w3-blue w3-padding-16 w3-round">
            <div class="w3-left"><i class="fa fa-share w3-xxxlarge"></i></div>
            <div class="w3-right"><h3><?=sanitize($en_cours) ?></h3></div>
            <div class="w3-clear"></div>
            <h4>En cours</h4>
        </div>
    </div>
    
    <div class="w3-third">
        <div class="w3-container w3-teal w3-padding-16 w3-round">
            <div class="w3-left"><i class="fa fa-check-circle w3-xxxlarge"></i></div>
            <div class="w3-right"><h3><?= sanitize($dispo_pct) . '%' ?></h3></div>
            <div class="w3-clear"></div>
            <h4>Disponibilité</h4>
        </div>
    </div>
</div>

<div class="w3-container w3-margin-top">
    <h3><b>Alertes de restitution</b></h3>
    <table class="w3-table w3-striped w3-white w3-border">
        <tr class="w3-red">
            <th>Nom</th>
            <th>Matériel</th>
            <th>Prévu le</th>
            <th>Retard</th>
        </tr>
        <?php if (empty($overdues)): ?>
            <tr>
                <td colspan="4">Aucun retard pour le moment.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($overdues as $o): ?>
                <?php
                    $prevue = formatDisplayDate(sanitize($o->date_prevue_restitution));
                    $jours = '';
                    $date_pre = DateTime::createFromFormat('Y-m-d', sanitize($o->date_prevue_restitution));
                    if ($date_pre) {
                        $diff = (new DateTime())->diff($date_pre);
                        $jours = '+' . $diff->days . ' jours';
                    }
                ?>
                <tr>
                    <td><?= sanitize($o->nom_emprunteur . ' ' . $o->prenom_emprunteur) ?></td>
                    <td><?= sanitize($o->nom_materiel) ?></td>
                    <td><?= sanitize($prevue) ?></td>
                    <td><b><?= sanitize($jours) ?></b></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>