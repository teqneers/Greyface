<?php

/**
 * Class AjaxRowsResult
 *
 * Defines a JSON formatted return string to answer on an AJAX request.
 * This includes the amount of total rows and the data of the requested set of data.
 */
class AjaxRowsResult extends AjaxResult {

    private $success = false;
    private $message = "";
    private $root = "data";
    private $rowsRoot = "rows";
    private $rowsTotal = 0;
    private $rows = array();

    public function __construct($rows, $rowsTotal, $rowsRoot="rows", $success=true, $message="", $root="data") {
        if (is_array($rows))
            $this->rows=$rows;
        $this->rowsTotal=$rowsTotal;
        $this->rowsRoot=$rowsRoot;
        $this->message= $message;
        $this->root = $root;
        $this->success = $success;
    }

    /**
     * @return string $success
     */
    private function getSuccessString() {
        return ($this->success)?"true":"false";
    }

    /**
     * Adds a row to the result set
     * 
     * @param array - $oneRow - one row of the result set.
     */
    public function addRow($oneRow) {
        if(is_array($oneRow))
            array_push($this->rows, $oneRow);
    }

    /**
     * Adds a row to the beginning of a result set.
     * 
     * @param array - $oneRow - one row of the result set.
     */
    public function prependRow($oneRow) {
        if(is_array($oneRow))
            array_unshift($this->rows, $oneRow);
    }

    /**
     * The standard __tooString method of a class
     * 
     * @return string - a string which represents the object and tells about the success and lists the included dataSet. Everything in proper json format.
     */
    public function __toString() {
        $string = "";
        $string .= '{ "' . $this->root . '":[{"success": ' . $this->getSuccessString() . ', "msg": "' . $this->message . '"}],';
        $string .= ' "' . $this->rowsRoot . '":[';
        foreach($this->rows as $row ) {
            $string .= json_encode($row) . ",";
        }

        $string = (substr($string, 0, -1 ) == "," ) ? substr($string, 0, -1) : $string; // cuts off the last comma!
        $string .= '],';
        $string .= ' "totalRows":' . (integer)$this->rowsTotal .'}';

        return $string;
    }
} 