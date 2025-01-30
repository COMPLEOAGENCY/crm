<?php
namespace Models;

class Question extends Model
{
    public static $TABLE_NAME = 'question';
    public static $TABLE_INDEX = 'questionid';
    public static $OBJ_INDEX = 'questionId';
    
    public static $SCHEMA = array(
        "questionId" => array(
            "field" => "questionid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "label" => array(
            "field" => "label",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        )
    );

    public function getQuestionByLabel($label) 
    {
        return $this->GetList([["label = ?", [$label]]], "", false, 1);
    }
}
