<?php
namespace Models\Chat;

use Models\Model;

class ChatAttachment extends Model
{
    public static $TABLE_NAME = 'chat_attachment';
    public static $TABLE_INDEX = 'chat_attachmentid';
    public static $OBJ_INDEX = 'chatAttachmentId';
    public static $SCHEMA = array(
        "chatAttachmentId" => array(
            "field" => "chat_attachmentid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "chatMessageId" => array(
            "field" => "chat_messageid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "fileName" => array(
            "field" => "file_name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "filePath" => array(
            "field" => "file_path",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "fileType" => array(
            "field" => "file_type",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        )
    );
}
