<?php
$db = require(dirname(__FILE__) . '/../../lib/mypdo.php');
$listFK = [];
$listMetadatas = [];
const DATABASE_PREVIEW_LIMIT = 200;


if (!isset($_SESSION)) {
  session_start();
}
define('SELECTED_YEAR', $_SESSION['annee']);

if (!myAuthClass::checkPriviledgeDatabase($_SESSION['user']['username'])) {
  header('location: index');
}

function getTables($db)
{
  global $listFK;
  global $listMetadatas;

  try {
    $query = "SELECT table_name ";
    $query .= "FROM information_schema.tables ";
    $query .= "WHERE table_schema = 'public' AND table_type = 'BASE TABLE' ";
    $query .= "ORDER by table_name";
    $statement = $db->query($query);
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    $table_list = "<div class='w3-bar w3-full w3-margin-top'>";
    $div_list = '';
    foreach ($result as $row) {
      $listMetadatas[sanitize($row['table_name'])] = getTableMetadata($db, sanitize($row['table_name']));

      $table_fk = getListFK($db, sanitize($row['table_name']));
      if (!is_null($table_fk)) {
        $listFK[sanitize($row['table_name'])] = $table_fk;
      }
    }
    foreach ($result as $row) {
      $tableName = sanitize($row['table_name']);

      $table_list .= "<button class='w3-bar-item w3-button w3-border w3-border-blue w3-blue' onclick=\"chooseTab(event, '$tableName')\">$tableName</button>";
      $div_list .= "<div id='div-$tableName' class='tabcontent w3-bordered w3-border w3-border-blue w3-padding w3-left-align w3-responsive' style='display: none;'>";
      $div_list .= "<div id='alerts-$tableName' class='w3-center'></div>";
      $div_list .= getTable($db, $tableName, $listMetadatas[$tableName]);
      $div_list .= "</div>";
    }
    $table_list .= "</div>";

    echo $table_list;
    echo $div_list;
  } catch (Throwable $e) {
    echo 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile();
  }
}

function getTable($db, $tableName, $columnMetadata)
{
  $table = getTableHead($columnMetadata, $tableName) . getTableValue($db, $tableName, $columnMetadata, DATABASE_PREVIEW_LIMIT);
  return $table;
}

function getTableHead($columnMetadata, $tableName)
{
  $table_list = "<button class='clickable w3-cyan' id='refresh-values-$tableName' onClick='refreshValues(\"$tableName\", " . json_encode($columnMetadata) . ")'><i class='material-icons' style='padding-top: 5px;'>&#xe5d5;</i> Refresh values</button>";
  $table_list .= "<table class='w3-table w3-bordered w3-border filter-table' id='filter-table-$tableName'><thead class='w3-light-gray'><tr>";
  $filter_list = "<div class='w3-responsive filter-inputs' style='display:flex;' id='filter-inputs-$tableName'>";
  foreach ($columnMetadata as $column) {
    $column_name = sanitize($column['name']);
    $column_type = sanitize($column['type']);

    $filter_list .= "<div class='w3-container' style='padding-left: 5px; padding-right: 5px;'>
    <label for='$column_name' class='w3-text-blue w3-left-align'>
    <p style='margin: 0;'><i>$column_name</i></p>
    </label>
    <input type='text' id='$column_name' class='w3-input w3-border w3-margin-bottom table-input' placeholder='$column_type' " . ($column_name === 'annee_scolaire' ? "value='" . SELECTED_YEAR . "'" : '') . ">
    </div>";
    $table_list .= "<th class='w3-border'><button class='w3-button' style='width:100%; height: 100%' onClick='sortTable(\"$tableName\", \"$column_name\", " . sanitize(json_encode($columnMetadata)) . ")'>$column_name</button></th>";
  }

  $table_list .= "<th colspan='2' class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button class='clickable w3-green' id='new-value-$tableName' onClick='newValue(\"$tableName\", " . json_encode($columnMetadata) . ")'><i class='material-icons' style='padding-top: 5px;'>&#xe145;</i></button></th>";
  $table_list .= "</thead>";
  $filter_list .= "</div>";

  $table_head = $filter_list;
  $table_head .= $table_list;

  return $table_head;
}

function getTableValue($db, $tableName, $columnMetadata, $limit = DATABASE_PREVIEW_LIMIT)
{
  global $listFK;

  try {
    $limit = (int)$limit;
    if ($limit <= 0) {
      $limit = DATABASE_PREVIEW_LIMIT;
    }

    $query = "SELECT * ";
    $query .= "FROM $tableName ";
    $query .= "LIMIT $limit";
    $statement = $db->query($query);

    $table_values = '<tbody id="table-values-' . $tableName . '">';
    $i = 1;
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
      $value_list = [];
      $table_values .= "<tr id='tr-$tableName-$i'>";
      foreach ($columnMetadata as $column) {
        $column_name = $column['name'];
        $column_type = $column['type'];
        $column_value = $row[$column_name];
        $value_list[$column_name] = $column_value;

        $table_values .= "<td class='w3-border' id='" . $column['name'] . "-$i'>" . validateTypeInbound($column_value, $column_type) . "</td>";
      }
      $table_values .= "<td class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button id='modify-value-$tableName-$i' class='clickable w3-light-green' onClick=\"modifyValue('$tableName', " . sanitize(json_encode($columnMetadata)) . ", $i, " . sanitize(json_encode($value_list)) . ")\"><i class='material-icons' style='padding-top: 5px;'>&#xe254;</i></button></td>";
      $table_values .= "<td class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button id='delete-value-$tableName-$i' class='clickable w3-red' onClick=\"deleteValue('$tableName', " . sanitize(json_encode($columnMetadata)) . ", " . sanitize(json_encode($listFK)) . ", $i, " . sanitize(json_encode($value_list)) . ")\"><i class='material-icons' style='padding-top: 5px;'>&#xe92b;</i></button></td>";
      $table_values .= "</tr>";
      $i += 1;
    }

    $statement->closeCursor();
    $loadedRows = $i - 1;
    if ($loadedRows >= $limit) {
      $colspan = count($columnMetadata) + 2;
      $table_values .= "<tr><td class='w3-border w3-pale-yellow' colspan='$colspan'>Affichage limité à $limit lignes. Utilise filtres/tri pour réduire volume.</td></tr>";
    }

    $table_values .= "</tbody></table>";

    return $table_values;
  } catch (Error | Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile();
  }
}

function getTableMetadata($db, $tableName)
{
  try {
    $query = "SELECT column_name as name, data_type as type, character_maximum_length as maximum, is_nullable as nullable ";
    $query .= "FROM information_schema.columns ";
    $query .= "WHERE table_name = :table_name ";
    $query .= "ORDER BY ordinal_position";
    $statement = $db->prepare($query);
    $statement->bindParam(':table_name', $tableName, PDO::PARAM_STR);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    $query_fk = "SELECT tc.constraint_name, tc.table_name, kcu.column_name, ccu.table_name AS foreign_table_name, ccu.column_name AS foreign_column_name ";
    $query_fk .= "FROM information_schema.table_constraints AS tc 
      JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
      JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name ";
    $query_fk .= "WHERE tc.constraint_type = 'FOREIGN KEY'
      AND tc.table_name = :table_name
      AND kcu.column_name = :column_name";

    $query_pk = "SELECT kcu.column_name ";
    $query_pk .= "FROM information_schema.table_constraints tc
      JOIN information_schema.key_column_usage kcu 
        ON tc.constraint_name = kcu.constraint_name ";
    $query_pk .= "WHERE tc.constraint_type = 'PRIMARY KEY'
      AND tc.table_name = :table_name
      AND kcu.column_name = :column_name";

    foreach ($result as &$column) {
      $statement = $db->prepare($query_fk);
      $statement->bindParam(':table_name', $tableName, PDO::PARAM_STR);
      $statement->bindParam(':column_name', $column['name'], PDO::PARAM_STR);
      $statement->execute();
      $column_fk = $statement->fetch(PDO::FETCH_ASSOC);
      $statement->closeCursor();

      $statement = $db->prepare($query_pk);
      $statement->bindParam(':table_name', $tableName, PDO::PARAM_STR);
      $statement->bindParam(':column_name', $column['name'], PDO::PARAM_STR);
      $statement->execute();
      $column_pk = $statement->fetch(PDO::FETCH_ASSOC);
      $statement->closeCursor();

      $column['fk'] = $column_fk;
      $column['pk'] = $column_pk;
    }
    unset($column);

    return $result;
  } catch (Throwable $e) {
    echo 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile();
  }
}

function getListFK($db, $tableName)
{
  try {
    $query_fk = "SELECT kcu.column_name, ccu.table_name AS foreign_table_name, ccu.column_name AS foreign_column_name ";
    $query_fk .= "FROM information_schema.table_constraints AS tc 
      JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
      JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name ";
    $query_fk .= "WHERE tc.constraint_type = 'FOREIGN KEY'
      AND tc.table_name = :table_name";

    $statement = $db->prepare($query_fk);
    $statement->bindParam(':table_name', $tableName, PDO::PARAM_STR);
    $statement->execute();
    $columns_fk = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    if ($columns_fk !== []) {
      return $columns_fk;
    }

    return null;
  } catch (Throwable $e) {
    echo 'Erreur: ' . $e->getMessage() . ' ligne -> ' . $e->getLine() . ' File - ' . $e->getFile();
  }
}

$pageTitle = 'Base de données';