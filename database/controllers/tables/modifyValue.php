<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/myproject.lib.php';
$db = require $root . '/lib/mypdo.php';

$tableName = GETPOST('tableName');
$values = GETPOST('values');
$oldValues = GETPOST('oldValues');
$columnMetadata = GETPOST('columnMetadata');

try {
  $search = [];
  $modified = [];
  $prepared = [];
  $i = 0;

  foreach ($columnMetadata as $column) {
    $key = $column['name'];
    $value = isset($values[$key]) ? $values[$key] : null;
    $validated_value = validateTypeOutbound($value, $column['type']);

    if (is_null($validated_value)) {
      $modified[] = "$key = NULL";
    } else {
      $modified[] = "$key = :param$i";
      $prepared[] = ["param$i", $validated_value, getInputType($column['type'], true)];
    }
    $i++;
  }

  foreach ($columnMetadata as $column) {
    $key = $column['name'];
    $value = isset($oldValues[$key]) ? $oldValues[$key] : null;
    $validated_value = validateTypeOutbound($value, $column['type']);

    if (is_null($validated_value)) {
      $search[] = "$key IS NULL";
    } else {
      $search[] = "$key = :param$i";
      $prepared[] = ["param$i", $validated_value, getInputType($column['type'], true)];
    }
    $i++;
  }

  $query = "UPDATE $tableName ";
  $query .= "SET " . implode(', ', $modified) . " ";
  $query .= "WHERE " . implode(' AND ', $search) . " ";

  $db->beginTransaction();
  $statement = $db->prepare($query);

  foreach ($prepared as $param) {
    $statement->bindValue(':' . $param[0], $param[1], $param[2]);
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
  if ($db instanceof PDO && $db->inTransaction()) {
    $db->rollBack();
  }
  $db = null;
  die(json_encode(['error' => 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile()]));
}
$db = null;