<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/project.lib.php';
$db = require $root . '/lib/pdo.php';

$tableName = GETPOST('tableName');
$values = GETPOST('values');
$columnMetadata = GETPOST('columnMetadata');

try {
  $columns = [];
  $columnNames = [];
  $prepared = [];
  $search = [];
  $i = 0;

  foreach ($values as $key => $value) {
    $validated_value = validateTypeOutbound($value, $columnMetadata[$i]['type']);
    $columnNames[] = $key;

    if (is_null($validated_value)) {
      $search[] = 'NULL';
    } else {
      $search[] = ":param$i";
      $prepared[] = ["param$i", $validated_value, getInputType($columnMetadata[$i]['type'], true)];
      $i++;
    }
  }

  $query = "INSERT INTO $tableName ";
  $query .= "(" . implode(', ', $columnNames) . ") ";
  $query .= "VALUES (" . implode(', ', $search) . ") ";

  $db->beginTransaction();
  $statement = $db->prepare($query);

  foreach ($prepared as $param) {
    $statement->bindParam($param[0], $param[1], $param[2]);
  }

  $statement->execute();
  $affectedRows = $statement->rowCount();
  if ($affectedRows === 0) {
    $db->rollBack();
    $db = null;
    die(json_encode(['error' => "Erreur lors de l'insertion"]));
  }

  $db->commit();
  echo json_encode(['success' => "Valeurs insérées avec succés"]);
} catch (Throwable $e) {
  $db->rollBack();
  $db = null;
  die(json_encode(['ERROR' => 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile()]));
}
$db = null;
