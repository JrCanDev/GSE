<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/project.lib.php';
$db = require $root . '/lib/pdo.php';

$value = GETPOST('value');
$fTable = GETPOST('fk_table');
$fColumn = GETPOST('fk_column');

try {
  $query = "SELECT $fColumn ";
  $query .= "FROM $fTable ";
  $statement = $db->query($query);
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $statement->closeCursor();

  $table_values = '';
  $valid = false;
  foreach ($result as $column) {
    $fk_value = $column[$fColumn];

    if ($value === null || $value === '') {
      $table_values .= "<div class='suggestion-item w3-button' style='width: 100%'>$fk_value</div>";
      $valid = true;
      continue;
    }

    $check_value = strtolower($value);

    if (strpos(strtolower($fk_value), $check_value) !== false) {
      $table_values .= "<div class='suggestion-item w3-button' style='width: 100%'>$fk_value</div>";
      $valid = true;
    }

    if (strpos(strtolower($fk_value), $check_value) !== false || is_null($value)) {
      $table_values .= "<div class='suggestion-item w3-button' style='width: 100%'>$fk_value</div>";
      $valid = true;
    }
  }
  $result = null;

  if (!$valid) {
    echo "Aucune correspondance dans la table $tableName";
  } else {
    echo $table_values;
  }
} catch (Throwable $e) {
  echo 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile();
  die();
}
$db = null;
