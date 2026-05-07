<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/project.lib.php';

$tableName = GETPOST('tableName');
$columnMetadata = GETPOST('columnMetadata');

if (!is_array($columnMetadata) || empty($columnMetadata)) {
  echo '';
  exit();
}

$inputAttributes = "data-table='$tableName' class='autofill'";
$table_values = '';
$table_values .= "<tr id='new-input-row-$tableName'>";
foreach ($columnMetadata as $column) {
  $column_name = $column['name'];
  $column_type = $column['type'];

  $inputType = getInputType($column_type);
  switch ($inputType) {
    case 'textarea':
      $table_values .= "<td class='w3-border' style='padding: 0;' id='td-$column_name'><textarea id='input-$column_name' $inputAttributes style='width: 100%; height: 100%;'></textarea></td>";
      break;

    default:
      $table_values .= "<td class='w3-border' style='padding: 0;' id='td-$column_name'><input id='input-$column_name' $inputAttributes type='$inputType' style='width: 100%; height: 51px;' value=''></td>";
      break;
  }
}
$table_values .= "<td class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button id='insert-value-$tableName-new' class='clickable w3-green' onClick=\"saveValue('$tableName', " . sanitize(json_encode($columnMetadata)) . ", true, true)\"><i class='material-icons' style='padding-top: 5px;'>&#xe5ca;</i></button></td>";
$table_values .= "<td class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button id='cancel-value-$tableName-new' class='clickable w3-red' onClick=\"saveValue('$tableName', " . sanitize(json_encode($columnMetadata)) . ", false, true)\"><i class='material-icons' style='padding-top: 5px;'>&#xe5c9;</i></button></td>";
$table_values .= "</tr>";
$result = null;

echo $table_values;