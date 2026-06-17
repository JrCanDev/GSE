<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/myproject.lib.php';
$db = require $root . '/lib/mypdo.php';

if (!isset($_SESSION)) {
  session_start();
}

if (!isUserAdmin()) {
  http_response_code(403);
  die(json_encode(['error' => 'Accès refusé.']));
}

$tableName = GETPOST('tableName');
$values = GETPOST('values');
$oldValues = GETPOST('oldValues');
$columnMetadata = GETPOST('columnMetadata');

$tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

try {
  $search = [];
  $modified = [];
  $prepared = [];
  $i = 0;

  foreach ($columnMetadata as $column) {
    $key = preg_replace('/[^a-zA-Z0-9_]/', '', $column['name']);
    
    if (isset($column['type']) && $column['type'] === 'bytea') {
      continue;
    }

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
    $key = preg_replace('/[^a-zA-Z0-9_]/', '', $column['name']);
    
    if (isset($column['type']) && $column['type'] === 'bytea') {
      continue;
    }

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

  if (empty($modified)) {
    die(json_encode(['error' => 'Aucune colonne modifiable valide détectée.']));
  }
  if (empty($search)) {
    die(json_encode(['error' => 'Impossible de cibler la ligne à modifier (aucune clause WHERE valide).']));
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
  
  $db->commit();
  
  if ($affectedRows === 0) {
    echo json_encode(['warning' => "Aucune modification réelle n'a été détectée (valeurs identiques)"]);
  } else {
    echo json_encode(['success' => "Valeurs modifiées avec succès"]);
  }

} catch (Throwable $e) {
  if ($db instanceof PDO && $db->inTransaction()) {
    $db->rollBack();
  }
  $db = null;
  die(json_encode(['error' => 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile()]));
}
$db = null;