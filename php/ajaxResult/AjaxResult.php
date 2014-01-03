<?php

/**
 * Class AjaxResult
 *
 * Defines a JSON formatted return string to answer on an AJAX request.
 */
class AjaxResult {

    private $success = false;
    private $message = "";
    private $root = "data";

    /**
     * The constructor
     *
     * @param boolean $success - Indicates weather the operation was successfully or not.
     * @param string $message - Tells what happened.
     * @param string $root - optional root element of AJAX return message.
     */
    public function __construct($success, $message, $root="data") {
        $this->success = (boolean) $success;
        $this->message= $message;
        $this->root = $root;
    }

    /**
     * @return string $success
     */
    private function getSuccessString() {
        return ($this->success) ? "true" : "false";
    }

    public function __toString() {
        return '{ "' . $this->root . '": [{"success": ' . $this->getSuccessString() . ', "msg": "' . $this->message . '"}]}';
    }

    /**
     * @return string - "The given data is incomplete"
     */
    public static function getIncompleteMsg() {
        return "The given data is incomplete.";
    }

    /**
     * @return string - "The given IP is invalid"
     */
    public static function getIpInvalidMsg() {
        return "The given IP is invalid.";
    }

    /**
     * @return string - "The given data is incomplete"
     */
    public static function getUnhandledActionMsg() {
        return "The requested action is not handled.";
    }
} 