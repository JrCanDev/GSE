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
$listFK = GETPOST('listFK');

$tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

try {
  $fk_table_names = [];
  if (!is_null($listFK)) {
    foreach ($columnMetadata as $i => $column) {
      $column_name = $column['name'];
      
      if (isset($column['type']) && $column['type'] === 'bytea') {
        continue;
      }

      foreach ($listFK as $table => $fk_columns) {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        if ($table === $tableName) continue;

        foreach ($fk_columns as $fk_column) {
          if ($fk_column['foreign_column_name'] === $column_name && array_search('id', array_column(array_column($columnMetadata, 'pk'), 'column_name')) !== false && array_search('id', array_column(array_column($columnMetadata, 'fk'), 'column_name')) === false) {
            
            $validatedValue = validateTypeOutbound($values[$column_name] ?? null, $columnMetadata[$i]['type'] ?? null);
            
            $fk_column_name = preg_replace('/[^a-zA-Z0-9_]/', '', $fk_column['column_name']);

            $query = "SELECT count(*) AS cnt FROM $table WHERE $fk_column_name = :fk";

            $statement = $db->prepare($query);
            $statement->bindValue(':fk', $validatedValue, PDO::PARAM_STR);
            $statement->execute();
            $nb_used = $statement->fetch(PDO::FETCH_ASSOC);
            $statement->closeCursor();
            if (!empty($nb_used) && ($nb_used['cnt'] ?? 0) > 0) {
              $fk_table_names[] = $table;
            }
          }
        }
      }
    }
  }

  if ($fk_table_names !== []) {
    $fk_table_names = array_unique($fk_table_names);
    if (count($fk_table_names) == 1) {
      $error = "Une clé est utilisée dans la table " . $fk_table_names[0];
    } else {
      $error = "Une clé est utilisée dans les tables " . implode(', ', $fk_table_names);
    }
    die(json_encode(['warning' => $error]) );
  }

  // build a lookup of metadata by column name to avoid relying on the order
  $metaByName = [];
  foreach ($columnMetadata as $meta) {
    if (isset($meta['name'])) $metaByName[$meta['name']] = $meta;
  }

  $pkColumns = array_values(array_filter($columnMetadata, function ($column) {
    return is_array($column['pk'] ?? null) && isset($column['pk']['column_name']);
  }));
  
  $deleteColumns = !empty($pkColumns) ? $pkColumns : array_filter($columnMetadata, function($col) {
    return ($col['type'] ?? '') !== 'bytea';
  });

  $search = [];
  $prepared = [];
  $i = 0;

  foreach ($deleteColumns as $column) {
    $key = preg_replace('/[^a-zA-Z0-9_]/', '', $column['name']);
    $value = $values[$key] ?? null;
    $type = $column['type'] ?? ($metaByName[$key]['type'] ?? null);
    
    // Si c'est du bytea, on l'exclut par sécurité
    if ($type === 'bytea') {
      continue;
    }

    $validatedValue = validateTypeOutbound($value, $type);

    // Empty string is valid only for text-like columns; other types must use NULL
    if ($value === '') {
      if (in_array($type, ['text', 'character varying', 'char'], true)) {
        $search[] = "$key = ''";
      } else {
        $search[] = "$key IS NULL";
      }
    } elseif (is_null($validatedValue)) {
      $search[] = "$key IS NULL";
    } else {
      $search[] = "$key = :param$i";
      $pdoType = getInputType($type, true);
      $prepared[] = ["param$i", $validatedValue, $pdoType];
    }
    $i++;
  }
  
  if (empty($search)) {
    die(json_encode(['error' => "Impossible de construire la condition de suppression (aucune colonne valide trouvée)."]));
  }

  $query = "DELETE FROM $tableName ";
  $query .= "WHERE " . implode(' AND ', $search) . " ";
  
  $statement = $db->prepare($query);
  foreach ($prepared as $param) {
    $statement->bindValue(':' . $param[0], $param[1], $param[2]);
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