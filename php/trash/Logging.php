<?php
/**
 * File containing the Logging class.
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
 * @internal	$Id: logging_class.php 1 2010-10-20 16:40:23Z teqneers-nico $
 * @copyright	Copyright (C) 2009-2010 TEQneers GmbH & Co. KG. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.txt
 */
/**
 * Logging class
 *
 * @package		Greytool
 * @subpackage	classes
 */
class Logging {
	#######################################################################
	# attributes
	#######################################################################
	/**
	 * The path and the data name of the logfile
	 *
	 * @var	string
	 */
	protected $_logfile = null;

	/**
	 * The file handler
	 *
	 * @var resource|boolean
	 */
	protected  $_fileHandle = null;


	#######################################################################
	# methods
	#######################################################################
	/**
	 * constructor
	 *
	 * Define the path for the log files
	 */
	public function __construct() {
		if(PATH_FS_APPLICATION_LOG){
			$this->_logfile = PATH_FS_APPLICATION_LOG.'/greyface.log';
		}
	}

	/**
	 * write the lod data to the folder
	 *
	 * Writes the Exception message into the log File
	 * Open the File and write the Message
	 *
	 * @throw	FileException
	 * @param	string	$message				Excpetion string
	 * @param	string	$file					Exception string
	 * @param	string	$line					Exception string
	 * @return	boolean							On success, return true otherwise FileException;
	 */
	public function writeLogFile ($message, $file, $line) {

		// If the current Data is not open yet, open it

		if( !$this->_logfile ) {

			return false;
		}

		$this->_fileHandler	= fopen($this->_logfile, 'a+');
		if(!$this->_fileHandler){
			throw new FileException('data could not be open', 10);
		}

		// Write down the Exception

		$string	= date('Y-m-d H:i:s',time()).'::'
					.strToUpper($file).' - '
					.$line.' - '
					.$message.' - '
					.$_SERVER['SCRIPT_FILENAME'].' User'
					.$_SESSION['username'].'\r\n';

		// Write to the File

		if(!fwrite( $this->_fileHandler, $string )){
			throw new FileException('data could not write Data', 10);
		}

		return true;
	}


	/**
	 * destructer
	 *
	 * Close the file handler
	 */
	public function __destruct() {
		if( $this->_fileHandle ) {
			fclose( $this->_fileHandle );
		}
	}


}
?>