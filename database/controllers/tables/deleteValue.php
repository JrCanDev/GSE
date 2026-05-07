<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/project.lib.php';
$db = require $root . '/lib/pdo.php';

$tableName = GETPOST('tableName');
$values = GETPOST('values');
$columnMetadata = GETPOST('columnMetadata');
$listFK = GETPOST('listFK');

try {
  $fk_table_names = [];
  foreach ($columnMetadata as $column) {
    $column_name = $column['name'];
    if (!is_null($listFK)) {
      foreach ($listFK as $table => $fk_columns) {
        if ($table !== $tableName) {
          foreach ($fk_columns as $fk_column) {
            if ($fk_column['foreign_column_name'] === $column_name && array_search('id', array_column(array_column($columnMetadata, 'pk'), 'column_name')) !== false && array_search('id', array_column(array_column($columnMetadata, 'fk'), 'column_name')) === false) {
              $validatedValue = validateTypeOutbound($values[$column_name], $columnMetadata[$i]['type']);
              $fk_column_name = $fk_column['column_name'];
              
              $query = "SELECT count($fk_column_name) ";
              $query .= "FROM $table ";
              $query .= "WHERE $fk_column_name = :fk";
              
              $statement = $db->prepare($query);
              $statement->bindParam(':fk', $validatedValue, PDO::PARAM_STR);
              $statement->execute();
              $nb_used = $statement->fetch(PDO::FETCH_ASSOC);
              $statement->closeCursor();
              if ($nb_used['count'] > 0) {
                $fk_table_names[] = $table;
              }
            }
          }
        }
      }
    }
  }

  if ($fk_table_names !== []) {
    if (count($fk_table_names) == 1) {
      $error = "Une clé est utilisé dans la table " . $fk_table_names[0];
    } else {
      $error = "Une clé est utilisé dans les tables " . implode(', ', $fk_table_names);
    }
    die(json_encode(['warning' => $error]) );
  }


  $search = [];
  $prepared = [];
  $i = 0;

  foreach ($values as $key => $value) {
    $validatedValue = validateTypeOutbound($value, $columnMetadata[$i]['type']);
    if (is_null($validatedValue)) {
      $search[] = "$key IS NULL";
    } else {
      $search[] = "$key = :param$i";
      $prepared[] = ["param$i", $validatedValue, getInputType($columnMetadata[$i]['type'], true)];
    }
    $i++;
  }
  
  $query = "DELETE FROM $tableName ";
  $query .= "WHERE " . implode(' AND ', $search) . " ";
  $statement = $db->prepare($query);

  foreach ($prepared as $param) {
    $statement->bindParam($param[0], $param[1], $param[2]);
  }

  $statement->execute();
  $affectedRows = $statement->rowCount();
  $statement->closeCursor();
  if ($affectedRows === 0) {
    die(json_encode(['warning' => "Rien n'a été supprimé"]) );
  }
  echo json_encode(['success' => $affectedRows]);
  $db = null;
} catch (Throwable $e) {
  $db = null;
  die(json_encode(['error' => 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile()]));
}