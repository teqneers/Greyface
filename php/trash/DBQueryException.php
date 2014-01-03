<?php
/**
 * File containing the DbQueryException class.
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		Greytool
 * @author		Nico Korthals <nico@teqneers.de>
 * @version		$Revision: 1 $
 * @internal	$Id: db_query_exception_class.php 1 2010-10-20 16:40:23Z teqneers-nico $
 * @copyright	Copyright (C) 2009-2010 TEQneers GmbH & Co. KG. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.txt
 */
/**
 * dbQueryException class
 *
 * @package		Greytool
 * @subpackage	classes
 */
class DbQueryException extends ApplicationException {

	#######################################################################
	# attributes
	#######################################################################
	/**
	 * The query message
	 *
	 * @var string
	 */
	protected $_query;

	#######################################################################
	# methods
	#######################################################################
	/**
	 * constructor
	 *
	 * @param	string	$errorMsg 		The error message for the exceptions
	 * @param	integer	$errorNum		The error number for the exception
	 * @param	integer	$query			The error query for the exception
	 */
	public function __construct($errorMsg, $errorNum, $query ) {
		$this->_query	= $query;
		parent::__construct($errorMsg. 'Query: '.$query,$errorNum);
	}

	/**
	 * __toString
	 *
	 * Overwrite the standard exception __toString of the exceptions class
	 *
	 * @return string			the error message
	 */
	public function __toString() {

		return '<br/> In class name:'.get_class($this) . "<br/>error message '{$this->message}' <br/>
				File: {$this->file}({$this->line})<br/>"
				. "{$this->getTraceAsString()}";
	}


}


?>