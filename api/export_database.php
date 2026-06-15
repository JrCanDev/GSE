<?php
$db = require_once(dirname(__FILE__) . '/../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../lib/myproject.lib.php');

if (!isUserAdmin()) { 
    header('location: ../index.php');
    exit(1);
}

if (!$db) {
   header('location: ../index.php');
   exit(1);
}

$filename = 'backup_' . $db_name . '_' . date('Y-m-d_H-i-s') . '.sql';

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "-- ========================================================\n";
echo "-- Export de la base de données : $db_name\n";
echo "-- Généré en pur PHP le " . date('Y-m-d H:i:s') . "\n";
echo "-- ========================================================\n\n";

try {
    echo "SET statement_timeout = 0;\n";
    echo "SET client_encoding = 'UTF8';\n";
    echo "SET standard_conforming_strings = on;\n";
    echo "SET check_function_bodies = false;\n";
    echo "SET xmloption = content;\n";
    echo "SET client_min_messages = warning;\n";
    echo "SET row_security = off;\n\n";

    echo "-- --------------------------------------------------------\n";
    echo "-- TYPES PERSONNALISÉS (ENUMs)\n";
    echo "-- --------------------------------------------------------\n\n";
    
    $typesStmt = $db->query("
        SELECT t.typname AS type_name, e.enumlabel AS enum_value
        FROM pg_type t 
        JOIN pg_enum e ON t.oid = e.enumtypid  
        JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace
        WHERE n.nspname = 'public'
        ORDER BY t.typname, e.enumsortorder
    ");
    
    $enums = [];
    while ($row = $typesStmt->fetch(PDO::FETCH_ASSOC)) {
        $enums[$row['type_name']][] = $db->quote($row['enum_value']);
    }

    foreach ($enums as $typeName => $values) {
        echo "DROP TYPE IF EXISTS " . quoteIdentifier($typeName) . " CASCADE;\n";
        echo "CREATE TYPE " . quoteIdentifier($typeName) . " AS ENUM (" . implode(', ', $values) . ");\n\n";
    }

    echo "-- --------------------------------------------------------\n";
    echo "-- STRUCTURE DES TABLES\n";
    echo "-- --------------------------------------------------------\n\n";

    $tablesStmt = $db->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
    ");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "DROP TABLE IF EXISTS " . quoteIdentifier($table) . " CASCADE;\n";
        echo "CREATE TABLE " . quoteIdentifier($table) . " (\n";

        $colStmt = $db->prepare("
            SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = ?
            ORDER BY ordinal_position
        ");
        $colStmt->execute([$table]);
        $columnsInfo = $colStmt->fetchAll(PDO::FETCH_ASSOC);

        $columnDefinitions = [];
        foreach ($columnsInfo as $col) {
            $type = $col['data_type'];
            if ($type === 'USER-DEFINED') {
                $udtStmt = $db->prepare("SELECT udt_name FROM information_schema.columns WHERE table_schema='public' AND table_name=? AND column_name=?");
                $udtStmt->execute([$table, $col['column_name']]);
                $type = $udtStmt->fetchColumn();
            }

            $def = "    " . quoteIdentifier($col['column_name']) . " " . $type;
            
            if (!empty($col['character_maximum_length']) && strpos($type, 'character') !== false) {
                $def .= "(" . $col['character_maximum_length'] . ")";
            }
            if ($col['is_nullable'] === 'NO') {
                $def .= " NOT NULL";
            }
            if ($col['column_default'] !== null) {
                $def .= " DEFAULT " . $col['column_default'];
            }
            $columnDefinitions[] = $def;
        }

        // Clé primaire
        $pkStmt = $db->prepare("
            SELECT kcu.column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
            WHERE tc.constraint_type = 'PRIMARY KEY' AND tc.table_schema = 'public' AND tc.table_name = ?
        ");
        $pkStmt->execute([$table]);
        $pkColumns = $pkStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($pkColumns)) {
            $escapedPkCols = array_map('quoteIdentifier', $pkColumns);
            $columnDefinitions[] = "    CONSTRAINT " . quoteIdentifier($table . "_pkey") . " PRIMARY KEY (" . implode(', ', $escapedPkCols) . ")";
        }

        echo implode(",\n", $columnDefinitions) . "\n";
        echo ");\n\n";
    }

    echo "-- --------------------------------------------------------\n";
    echo "-- INSERTION DES DONNÉES\n";
    echo "-- --------------------------------------------------------\n\n";

    echo "SET session_replication_role = 'replica';\n\n";

    foreach ($tables as $table) {
        $dataStmt = $db->query("SELECT * FROM " . quoteIdentifier($table));
        while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
            $columns = array_keys($row);
            $escapedColumns = array_map('quoteIdentifier', $columns);
            
            $values = array_values($row);
            $escapedValues = array_map(function($value) use ($db) {
                if ($value === null) return 'NULL';
                if (is_bool($value)) return $value ? 'true' : 'false';
                return $db->quote($value);
            }, $values);

            echo "INSERT INTO " . quoteIdentifier($table) . " (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
        }
        echo "\n";
    }

    echo "SET session_replication_role = 'origin';\n\n";

    echo "-- --------------------------------------------------------\n";
    echo "-- CLÉS ÉTRANGÈRES (ALTER TABLE)\n";
    echo "-- --------------------------------------------------------\n\n";

    $fkStmt = $db->query("
        SELECT tc.table_name AS table_source, tc.constraint_name, kcu.column_name AS colonne_source, ccu.table_name AS table_cible, ccu.column_name AS colonne_cible
        FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
        JOIN information_schema.constraint_column_usage ccu ON ccu.constraint_name = tc.constraint_name AND ccu.table_schema = tc.table_schema
        WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'public'
    ");
    $foreignKeys = $fkStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($foreignKeys as $fk) {
        echo "ALTER TABLE " . quoteIdentifier($fk['table_source']) . "\n";
        echo "    ADD CONSTRAINT " . quoteIdentifier($fk['constraint_name']) . " \n";
        echo "    FOREIGN KEY (" . quoteIdentifier($fk['colonne_source']) . ") \n";
        echo "    REFERENCES " . quoteIdentifier($fk['table_cible']) . "(" . quoteIdentifier($fk['colonne_cible']) . ");\n\n";
    }

    echo "-- --------------------------------------------------------\n";
    echo "-- VUES (VIEWS)\n";
    echo "-- --------------------------------------------------------\n\n";

    $viewsStmt = $db->query("
        SELECT table_name AS view_name, view_definition 
        FROM information_schema.views 
        WHERE table_schema = 'public'
    ");
    while ($view = $viewsStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "DROP VIEW IF EXISTS " . quoteIdentifier($view['view_name']) . " CASCADE;\n";
        echo "CREATE VIEW " . quoteIdentifier($view['view_name']) . " AS\n" . rtrim($view['view_definition'], ';') . ";\n\n";
    }

    echo "-- --------------------------------------------------------\n";
    echo "-- FONCTIONS ET PROCÉDURES\n";
    echo "-- --------------------------------------------------------\n\n";

    $funcsStmt = $db->query("
        SELECT p.proname AS func_name, pg_get_functiondef(p.oid) AS func_def
        FROM pg_proc p
        JOIN pg_namespace n ON p.pronamespace = n.oid
        WHERE n.nspname = 'public'
          AND p.prokind = 'f' -- 'f' pour fonction classique (exclut les procédures/agrégats complexes)
    ");
    while ($func = $funcsStmt->fetch(PDO::FETCH_ASSOC)) {
        // pg_get_functiondef renvoie le bloc entier 'CREATE OR REPLACE FUNCTION ...'
        echo $func['func_def'] . ";\n\n";
    }

    echo "-- --------------------------------------------------------\n";
    echo "-- TRIGGERS\n";
    echo "-- --------------------------------------------------------\n\n";

    $triggersStmt = $db->query("
        SELECT tgname AS trigger_name, tgrelid::regclass AS table_name, pg_get_triggerdef(oid) AS trigger_def
        FROM pg_trigger
        WHERE tgisinternal = false
    ");
    while ($trig = $triggersStmt->fetch(PDO::FETCH_ASSOC)) {
        echo "DROP TRIGGER IF EXISTS " . quoteIdentifier($trig['trigger_name']) . " ON " . quoteIdentifier($trig['table_name']) . ";\n";
        echo $trig['trigger_def'] . ";\n\n";
    }

    echo "-- FIN DE L'EXPORT FULL AUTONOME --\n";

} catch (Exception $e) {
    echo "\n-- ERREUR CRITIQUE DURANT L'EXPORT SYSTEM : " . $e->getMessage() . "\n";
}

exit;

function quoteIdentifier($field) {
    return '"' . str_replace('"', '""', $field) . '"';
}