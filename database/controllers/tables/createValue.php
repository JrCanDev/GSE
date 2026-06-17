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
$columnMetadata = GETPOST('columnMetadata');

$tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

try {
  $columnNames = [];
  $prepared = [];
  $search = [];
  $i = 0;

  foreach ($columnMetadata as $column) {
    $key = preg_replace('/[^a-zA-Z0-9_]/', '', $column['name']);
    
    if (isset($column['type']) && $column['type'] === 'bytea') {
      continue;
    }

    $value = isset($values[$key]) ? $values[$key] : null;
    $validated_value = validateTypeOutbound($value, $column['type']);
    $columnNames[] = $key;

    if (is_null($validated_value)) {
      $search[] = 'NULL';
    } else {
      $search[] = ":param$i";
      $prepared[] = ["param$i", $validated_value, getInputType($column['type'], true)];
    }
    $i++;
  }

  if (empty($columnNames)) {
    die(json_encode(['error' => "Aucune colonne valide à insérer."]));
  }

  $query = "INSERT INTO $tableName ";
  $query .= "(" . implode(', ', $columnNames) . ") ";
  $query .= "VALUES (" . implode(', ', $search) . ") ";

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
    die(json_encode(['error' => "Erreur lors de l'insertion"]));
  }

  $db->commit();
  echo json_encode(['success' => "Valeurs insérées avec succès"]);
} catch (Throwable $e) {
  if ($db instanceof PDO && $db->inTransaction()) {
    $db->rollBack();
  }
  $db = null;
  die(json_encode(['error' => 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile()]));
}
$db = null;