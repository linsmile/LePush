<?php
namespace LePush\Exceptions;

class APIConnectionException extends LePushException {

    function __toString() {
        return "\n" . __CLASS__ . " -- {$this->message} \n";
    }
}
