<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/project.lib.php';

$tableName = GETPOST('tableName');
$nbRow = GETPOST('row');
$tableValues = GETPOST('values');
$columnMetadata = GETPOST('columnMetadata');

$inputAttributes = "data-table='$tableName' class='autofill'";
$table_values = '';
$default_value_list = [];
foreach ($columnMetadata as $column) {
  $column_name = $column['name'];
  $column_type = $column['type'];
  $column_value = $tableValues[$column_name];
  $default_value_list[$column_name] = $column_value;

  $inputType = getInputType($column_type);
  switch ($inputType) {
    case 'textarea':
      $table_values .= "<td class='w3-border' style='padding: 0;' id='td-$column_name'><textarea id='input-$column_name' $inputAttributes style='width: 100%; height: 100%;'>" . validateTypeInbound($column_value, $column_type) . "</textarea></td>";
      break;

    default:
      $table_values .= "<td class='w3-border' style='padding: 0;' id='td-$column_name'><input id='input-$column_name' $inputAttributes type='$inputType' style='width: 100%; height: 51px;' value='" . validateTypeInbound($column_value, $column_type) . "'></td>";
      break;
  }
}
$table_values .= "<td class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button id='accept-value-$tableName-$nbRow' class='clickable w3-green' onClick=\"saveValue('$tableName', " . sanitize(json_encode($columnMetadata)) . ", true, false, $nbRow, " . sanitize(json_encode($default_value_list)) . ")\"><i class='material-icons' style='padding-top: 5px;'>&#xe5ca;</i></button></td>";
$table_values .= "<td class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button id='revert-value-$tableName-$nbRow' class='clickable w3-red' onClick=\"saveValue('$tableName', " . sanitize(json_encode($columnMetadata)) . ", false, false, $nbRow, " . sanitize(json_encode($default_value_list)) . ")\"><i class='material-icons' style='padding-top: 5px;'>&#xe5c9;</i></button></td>";

$result = null;

echo $table_values;
