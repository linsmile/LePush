<?php
namespace LePush\Exceptions;

class LePushException extends \Exception {

    function __construct($message) {
        parent::__construct($message);
    }
}
