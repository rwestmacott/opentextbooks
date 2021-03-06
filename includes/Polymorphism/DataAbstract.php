<?php
/**
 * Project: opentextbooks
 * Project Sponsor: BCcampus <https://bccampus.ca>
 * Copyright 2012-2016 Brad Payne <https://bradpayne.ca>
 * Date: 2016-05-31
 * Licensed under GPLv3, or any later version
 *
 * @author Brad Payne
 * @package OPENTEXTBOOKS
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2012-2016, Brad Payne
 */

namespace BCcampus\OpenTextBooks\Polymorphism;

use BCcampus\OpenTextBooks\Models\Storage;

abstract class DataAbstract {

	/**
	 * @return mixed
	 */
	abstract function getResponses();

	/**
	 * @param $location
	 * @param $file_name
	 * @param $file_type
	 * @param $serialize
	 *
	 * @return Storage\Cache|bool
	 */
	protected function checkStorage( $location, $file_name, $file_type, $serialize ) {
		$storage = Storage\Cache::create( OTB_DIR . $location, $file_name, $file_type, $serialize );

		// check if there is a stored version of the results
		if ( $storage->fileExists() && ! $storage->expiredCache() && $storage->getFileSize() > 10 ) {
			return $storage;
		} else {
			return false;
		}
	}

	/**
	 * @param $location
	 * @param $file_name
	 * @param $file_type
	 * @param $data
	 * @param bool $serialize
	 */
	protected function saveToStorage( $location, $file_name, $file_type, $data, $serialize = false ) {
		$storage = Storage\Cache::create( OTB_DIR . $location, $file_name, $file_type, $serialize );
		$storage->save( $data );

		// remove if there is nothing in it
		if ( $storage->getFileSize() < 8 ) {
			$storage->remove();
		}
	}

}