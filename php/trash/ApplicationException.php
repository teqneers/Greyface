<?php
/**
 * File containing the ApplicationException class.
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
 * @internal	$Id: application_exception_class.php 1 2010-10-20 16:40:23Z teqneers-nico $
 * @copyright	Copyright (C) 2009-2010 TEQneers GmbH & Co. KG. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.txt
 */
/**
 * ApplicationException class
 *
 * @package		Greytool
 * @subpackage	classes
 */
class ApplicationException extends Exception {
	#######################################################################
	# attributes
	#######################################################################


	/**
	 * Original message, before escaping
	 *
	 * @var string
	 */
	public $originalMessage;

	/**
	 * Contains the error message
	 *
	 * @var integer
	 */
	public $originalNum;

	/**
	 * Log the exception true or false
	 *
	 * @var  boolean
	 */
	protected $_logging = true;

	/**
	 * Log object
	 *
	 * @var  object
	 */
	protected $_log;


	#######################################################################
	# methods
	#######################################################################


	/**
	 * constructor
	 *
	 * @param 	string 		$message		The error message for the exceptions
	 * @param	integer		$errorNum		The error number for the exception
	 * @return boolean						true or false
	 */
	public function __construct( $message, $errorNum=0 ) {
		$this->originalNum		= $errorNum;
		$this->originalMessage	= $message;
		parent::__construct( $this->originalMessage, $this->originalNum);
		$this->log();
	}

	/**
	 * log
	 *
	 * This function calls the logg fuinction that the error message can put into
	 * the file.
	 */
	public function log() {
		$this->_log = new Logging;
		$this->_log->writeLogFile($this->getMessage(), $this->getFile(), $this->getLine());
	}

}
?>
