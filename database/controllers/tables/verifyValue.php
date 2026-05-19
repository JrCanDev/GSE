<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/myproject.lib.php';

$tableName = GETPOST('tableName');
$tableValues = GETPOST('values');
$columnMetadata = GETPOST('columnMetadata');
$oldId = GETPOSTISSET('modify') ? GETPOST('modify') : false;

if (!is_array($tableValues) || !is_array($columnMetadata) || empty($columnMetadata)) {
  die(json_encode(['error' => 'Données invalides pour la vérification']));
}

$error = null;
$valueList = [];
$nb_pk = countKeyOccurrences($columnMetadata, 'pk');

try {
  $db = require $root . '/lib/mypdo.php';
} catch (Throwable $e) {
  die(json_encode(['error' => 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile()]));
}

foreach ($columnMetadata as $column) {
  $column_name = $column['name'];
  $column_type = $column['type'];
  $maximum_characters = $column['maximum'];
  $is_nullable = $column['nullable'];
  $foreign_key = $column['fk'];
  $primary_key = $column['pk'];
  $column_value = array_key_exists($column_name, $tableValues) ? $tableValues[$column_name] : null;
  $validated_value = validateTypeOutbound($column_value, $column_type);
  $valueList[$column_name] = $validated_value;

  switch ($column_type) {
    case 'boolean':
      if (!in_array($validated_value, ['true', 't', 'oui', 'bool(true)', true, 1, 'false', 'f', 'non', 'bool(false)', false, 0], 1)) {
        $error = "$column_name doit être une valeur entre 'true' | 't' | 'oui' | 'false' | 'f' | 'non'";
      }
      break;
    case 'char':
      if (!is_null($validated_value) && strlen((string)$validated_value) > 1) {
        $error = "$column_name doit être un charactère unique";
      }
      break;
    default:
      if ($is_nullable == 'NO') {
        if (is_null($validated_value)) {
          $error = "$column_name ne peut pas être vide";
        }
      }
      if (!$error) {
        if ((!is_null($maximum_characters) && $maximum_characters !== "") && strlen((string)$validated_value) > $maximum_characters) {
          $error = "$column_name ne peut pas être plus grand que $maximum_characters charactères: $validated_value -> " . strlen((string)$validated_value);
        }
      }
      break;
  }

  try {
    $hasForeignKey = is_array($foreign_key) && isset($foreign_key['foreign_table_name'], $foreign_key['foreign_column_name']);
    if ($hasForeignKey && !$error && !is_null($validated_value)) {
      $table_fk = $foreign_key['foreign_table_name'];
      $column_fk = $foreign_key['foreign_column_name'];

      $query = "SELECT count($column_fk) ";
      $query .= "FROM $table_fk ";
      $query .= "WHERE $column_fk = :fk";
      $statement = $db->prepare($query);
      $statement->bindParam(':fk', $validated_value, getInputType($column_type, true));
      $statement->execute();
      $result = $statement->fetch(PDO::FETCH_ASSOC);
      $statement->closeCursor();

      if ($result['count'] < 1) {
        $error = "$validated_value n'existe pas dans la table $table_fk";
      }
      $result = null;
    }

    $hasPrimaryKey = is_array($primary_key) && isset($primary_key['column_name']);
    $oldValue = (is_array($oldId) && array_key_exists($column_name, $oldId)) ? validateTypeOutbound($oldId[$column_name], $column_type) : null;
    if ($hasPrimaryKey && !$error && $oldId !== false && $validated_value !== $oldValue && $nb_pk <= 1) {
      $query = "SELECT count($column_name) ";
      $query .= "FROM $tableName ";
      $query .= "WHERE $column_name = :pk";
      $statement = $db->prepare($query);
      $statement->bindParam(':pk', $validated_value, getInputType($column_type, true));
      $statement->execute();
      $result = $statement->fetch(PDO::FETCH_ASSOC);
      $statement->closeCursor();

      if ($result['count'] > 0) {
        $error = "$validated_value existe déjà dans la table $tableName";
      }
      $result = null;
    }
  } catch (Throwable $e) {
    $db = null;
    die(json_encode(['error' => 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile()]));
  }
}

$db = null;
if (!is_null($error)) {
  die(json_encode(['warning' => $error]));
}

echo json_encode(['success' => $valueList]);
