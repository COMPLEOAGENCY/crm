<?php
namespace Models;

class Administration extends Model
{
    public static $TABLE_NAME = 'administration';
    public static $TABLE_INDEX = 'administrationid';
    public static $OBJ_INDEX = 'administrationid';
    public static $SCHEMA = array(
        "administrationid" => array(
            "field" => "administrationid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "name" => array(
            "field" => "name",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "label" => array(
            "field" => "label",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "value" => array(
            "field" => "value",
            "fieldType" => "text",
            "type" => "string",
            "default" => null
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }

    /**
     * Gets an Administration object by label.
     * @param string $label
     * @return Administration|null
     */
    public function getByLabel(string $label)
    {
        $results = $this->getList(1, ['label' => $label]);
        return !empty($results) ? $this->hydrate((array)$results[0]) : null;
    }
}
