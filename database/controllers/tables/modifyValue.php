<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/project.lib.php';
$db = require $root . '/lib/pdo.php';

$tableName = GETPOST('tableName');
$values = GETPOST('values');
$oldValues = GETPOST('oldValues');
$columnMetadata = GETPOST('columnMetadata');

try {
  $search = [];
  $modified = [];
  $prepared = [];
  $i = 0;
  $j = 0;

  foreach ($values as $key => $value) {
    $validated_value = validateTypeOutbound($value, $columnMetadata[$i]['type']);

    if (is_null($validated_value)) {
      $modified[] = "$key = NULL";
    } else {
      $modified[] = "$key = :param$i";
      $prepared[] = ["param$i", $validated_value, getInputType($columnMetadata[$i]['type'], true)];
      $i++;
    }
  }

  foreach ($oldValues as $key => $value) {
    $validated_value = validateTypeOutbound($value, $columnMetadata[$j]['type']);

    if (is_null($validated_value)) {
      $search[] = "$key IS NULL";
    } else {
      $search[] = "$key = :param$i";
      $prepared[] = ["param$i", $validated_value, getInputType($columnMetadata[$j]['type'], true)];
      $i++;
      $j++;
    }
  }

  $query = "UPDATE $tableName ";
  $query .= "SET " . implode(', ', $modified) . " ";
  $query .= "WHERE " . implode(' AND ', $search) . " ";

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
    die(json_encode(['error' => 'Erreur lors de la modification']));
  }

  $db->commit();
  echo json_encode(['success' => "Valeurs modifiées avec succés"]);

} catch (Throwable $e) {
  $db->rollBack();
  $db = null;
  die(json_encode(['error' => 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile()]));
}
$db = null;