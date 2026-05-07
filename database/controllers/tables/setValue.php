<?php
$root = dirname(__FILE__) . '/../../..';
include_once $root . '/vendor/autoload.php';
require_once $root . '/lib/project.lib.php';

$tableName = GETPOST('tableName');
$nbRow = GETPOST('row');
$tableValues = GETPOST('values');
$columnMetadata = GETPOST('columnMetadata');
$listFK = GETPOST('listFK');

$table_values = '';
foreach ($columnMetadata as $column) {
  $column_name = $column['name'];
  $column_type = $column['type'];
  $column_value = $tableValues[$column_name];
  $value_list[$column_name] = $column_value;

  $table_values .= "<td class='w3-border' id='". $column_name . "'>" . validateTypeInbound($column_value, $column_type) . "</td>";
}
$table_values .= "<td class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button id='modify-value-$tableName-$nbRow' class='clickable w3-light-green' onClick=\"modifyValue('$tableName', " . sanitize(json_encode($columnMetadata)) . ", $nbRow, " . sanitize(json_encode($value_list)) . ")\"><i class='material-icons' style='padding-top: 5px;'>&#xe254;</i></button></td>";
$table_values .= "<td class='w3-blue-grey w3-border-blue-grey' style='width: 5%'><button id='delete-value-$tableName-$nbRow' class='clickable w3-red' onClick=\"deleteValue('$tableName', " . sanitize(json_encode($columnMetadata)) . ", " . sanitize(json_encode($listFK)) . ", $nbRow, " . sanitize(json_encode($value_list)) . ")\"><i class='material-icons' style='padding-top: 5px;'>&#xe92b;</i></button></td>";
$result = null;

echo $table_values;