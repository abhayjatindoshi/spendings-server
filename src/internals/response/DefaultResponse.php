<?php
namespace encryptorcode\response;

use encryptorcode\exception\ExceptionResponse as ExceptionResponse;

class DefaultResponse{

    public static function invalidId(){
        throw new ExceptionResponse(
            "INVALID_ID",
            "You have provided an invalid id",
            400
        );
    }

    public static function invalidPortal(){
        throw new ExceptionResponse(
            "INVALID_PORTAL",
            "You have provided an invalid portal id",
            400
        );
    }

    public static function updateSuccess(){
        return new JsonResponse(array(
            "code" => "UPDATED_SUCESSFULLY",
            "message" => "Updated successfully.",
            "status" => "SUCCESS"
        ));
    }

    public static function updateFailed(){
        return new JsonResponse(array(
            "code" => "UPDATE_FAILED",
            "message" => "Failed to update.",
            "status" => "FAILED"
        ));
    }

    public static function updateForbidden($model){
        throw new ExceptionResponse(
            strtoupper($model)."_UPDATE_FORBIDDEN",
            "You are not allowed to update this ".strtolower($model),
            403
        );
    }

    public static function deleteSuccess(){
        return new JsonResponse(array(
            "code" => "DELETED_SUCESSFULLY",
            "message" => "Deleted successfully.",
            "status" => "SUCCESS"
        ));
    }

    public static function deleteFailed(){
        return new JsonResponse(array(
            "code" => "DELETE_FAILED",
            "message" => "Failed to delete.",
            "status" => "FAILED"
        ));
    }

    public static function deleteForbidden($model){
        throw new ExceptionResponse(
            strtoupper($model)."_UPDATE_FORBIDDEN",
            "You are not allowed to update this ".strtolower($model),
            403
        );
    }

}
