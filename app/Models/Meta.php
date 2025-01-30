<?php
namespace Models;

class Meta extends Model
{
    public static $TABLE_NAME = 'meta_table_data';
    public static $TABLE_INDEX = 'data_id';
    public static $OBJ_INDEX = 'dataId';
    
    public static $SCHEMA = array(
        "dataId" => array(
            "field" => "data_id",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "tableId" => array(
            "field" => "table_id",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "typeId" => array(
            "field" => "type_id",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "dataRow" => array(
            "field" => "data_row",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "dataVal" => array(
            "field" => "data_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        )
    );

    /**
     * Récupère toutes les métadonnées pour une ligne
     * 
     * @param int $rowId ID de la ligne
     * @param string $tableName Nom de la table (par défaut 'lead')
     * @param bool $asArray Si true, retourne un tableau associatif [label => value], sinon retourne un tableau d'objets
     * @return array
     */
    public function getAllMetaForRow($rowId, $tableName = 'lead', $asArray = true) 
    {
        $db = Database::instance();
        $raw = [
            "mt.type_nom as label",
            "mtd.data_val as value"
        ];
        
        $results = $db->buildQuery('meta_table_data as mtd')
            ->join('meta_type as mt', 'mt.type_id', '=', 'mtd.type_id')
            ->join('meta_tables as mta', 'mta.table_id', '=', 'mtd.table_id')
            ->where([
                'mtd.data_row' => $rowId,
                'mta.table_nom' => $tableName
            ])
            ->select($raw)
            ->get()
            ->toArray();

        if ($asArray) {
            $meta = [];
            foreach ($results as $row) {
                $meta[$row->label] = $row->value;
            }
            return $meta;
        }

        return $results;
    }

    /**
     * Ajoute ou met à jour une valeur de métadonnée
     * 
     * @param string $tableName Nom de la table
     * @param int $rowId ID de la ligne
     * @param string $label Label de la métadonnée
     * @param mixed $value Valeur à enregistrer
     * @return bool
     */
    public function addRowValue($tableName, $rowId, $label, $value)
    {
        // Récupérer l'ID de la table
        $tableId = $this->getTableId($tableName);
        if (!$tableId) {
            return false;
        }

        // Récupérer l'ID du type (label)
        $typeId = $this->getTypeId($label, $tableId);
        if (!$typeId) {
            return false;
        }

        $db = Database::instance();
        
        // Vérifier si la métadonnée existe déjà
        $existing = $db->buildQuery('meta_table_data')
            ->where([
                'table_id' => $tableId,
                'data_row' => $rowId,
                'type_id' => $typeId
            ])
            ->first();

        if ($existing) {
            // Mise à jour
            return $db->updateOrInsert(
                'meta_table_data',
                'data_id',
                ['data_id' => $existing->data_id],
                ['data_val' => $value]
            );
        } else {
            // Insertion
            return $db->updateOrInsert(
                'meta_table_data',
                'data_id',
                [],
                [
                    'table_id' => $tableId,
                    'data_row' => $rowId,
                    'type_id' => $typeId,
                    'data_val' => $value
                ]
            );
        }
    }

    /**
     * Supprime une métadonnée
     * 
     * @param string $tableName Nom de la table
     * @param int $rowId ID de la ligne
     * @param string $label Label de la métadonnée
     * @return bool
     */
    public function deleteRowValue($tableName, $rowId, $label)
    {
        $tableId = $this->getTableId($tableName);
        if (!$tableId) {
            return false;
        }

        $typeId = $this->getTypeId($label, $tableId);
        if (!$typeId) {
            return false;
        }

        $db = Database::instance();
        return $db->delete('meta_table_data', [
            'table_id' => $tableId,
            'data_row' => $rowId,
            'type_id' => $typeId
        ]);
    }

    /**
     * Récupère l'ID d'une table par son nom
     * 
     * @param string $tableName Nom de la table
     * @return int|false
     */
    private function getTableId($tableName)
    {
        $db = Database::instance();
        $result = $db->buildQuery('meta_tables')
            ->where(['table_nom' => $tableName])
            ->first();
        
        return $result ? $result->table_id : false;
    }

    /**
     * Récupère l'ID d'un type par son label et l'ID de sa table
     * 
     * @param string $label Label du type
     * @param int $tableId ID de la table
     * @return int|false
     */
    private function getTypeId($label, $tableId)
    {
        $db = Database::instance();
        $result = $db->buildQuery('meta_type')
            ->where([
                'type_nom' => $label,
                'table_id' => $tableId
            ])
            ->first();
        
        return $result ? $result->type_id : false;
    }
}
