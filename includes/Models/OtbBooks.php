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
 *
 * Main goal is to retrieve data from either storage or
 * an API request, set instance variables with that data
 *
 * uses Delegation design pattern and dependency injection
 * of an interface to lessen the pain when switching the api
 * at some point in the future.
 *
 */
namespace BCcampus\OpenTextBooks\Models;

use BCcampus\OpenTextBooks\Polymorphism;

class OtbBooks extends Polymorphism\DataAbstract {
	private $defaultArgs = array(
		'subject'        => '',
		'uuid'           => '',
		'search'         => '',
		'start'          => '',
		'contributor'    => '',
		'keyword'        => '',
		'lists'          => '',
		'stats'          => '',
		'collectionUuid' => '',
	);
	protected $args = array();
	protected $api;
	private $location = 'cache/catalogue';
	private $type = 'txt';
	private $data;
	const ALL_RECORDS = '_ALL';

	/**
	 * OtbBooks constructor.
	 *
	 * @param Polymorphism\RestInterface $api
	 * @param array $args
	 */
	public function __construct( Polymorphism\RestInterface $api, $args ) {
		if ( is_array( $args ) ) {
			// let the args override the default args
			$this->args = array_merge( $this->defaultArgs, $args );
		}
		$this->api = $api;

		try {
			$this->retrieve();
		} catch ( \Exception $exc ) {
			error_log( $exc->getMessage(), 0 );
		}

	}

	/**
	 *
	 * @throws \Exception
	 */
	private function retrieve() {

		try {
			$this->setResponses();
		} catch ( \Exception $exp ) {
			error_log( $exp->getMessage(), 0 );
		}

	}

	/**
	 *
	 */
	private function setResponses() {
		$serialize = true;
		$file_name = $this->setFileName();
		$file_type = $this->type;

		$persistent_data = $this->checkStorage( $this->location, $file_name, $file_type, $serialize );

		// check if there is a stored version of the results
		if ( $persistent_data ) {
			$this->data = $persistent_data->load();
		} else {
			// request an API response
			$this->data = $this->api->retrieve( $this->args );
			$this->saveToStorage( $this->location, $file_name, $file_type, $this->data, $serialize );
		}

	}

	/**
	 * @return mixed|string
	 */
	private function setFileName() {
		$name = '';
		// name file after the collection
		if ( empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) && empty( $this->args['search'] ) ) {
			$name = $this->args['collectionUuid'];
		} // individual record
		elseif ( ! empty( $this->args['uuid'] ) ) {
			$name = $this->args['uuid'];
		} // name the file after the search term
		elseif ( empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) && ! empty( $this->args['search'] ) ) {
			$name = $this->args['collectionUuid'] . $this->args['search'];
		} // name the file after the subject area
		elseif ( ! empty( $this->args['subject'] ) && empty( $this->args['uuid'] ) ) {
			$name = $this->args['collectionUuid'] . $this->args['subject'] . $this->args['search'];
		} // name the file after the subject area and search term
		elseif ( ! empty( $this->args['subject'] ) && ! empty( $this->args['search'] ) ) {
			$name = $this->args['subject'] . $this->args['search'];
		}

		return $name;
	}

	/**
	 * @return mixed
	 */
	public function getResponses() {
		return $this->data;

	}

	/**
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * @return array
	 */
	public function getPrunedResults() {
		$pruned = array();

		// if there are many
		if ( array_key_exists( 0, $this->data ) ) {
			foreach ( $this->data as $key => $item ) {
				$pruned[ $key ]['name']         = $item['name'];
				$pruned[ $key ]['uuid']         = $item['uuid'];
				$pruned[ $key ]['createdDate']  = $item['createdDate'];
				$pruned[ $key ]['modifiedDate'] = $item['modifiedDate'];
			}
			// if there is only one    
		} else {
			$pruned['name']         = $this->data['name'];
			$pruned['uuid']         = $this->data['uuid'];
			$pruned['createdDate']  = $this->data['createdDate'];
			$pruned['modifiedDate'] = $this->data['modifiedDate'];
		}

		return $pruned;
	}

	/**
	 * @return array
	 */
	public function getUuids() {
		$uuids = array();

		// if there are many
		if ( array_key_exists( 0, $this->data ) ) {
			foreach ( $this->data as $item ) {
				$uuids[] = $item['uuid'];
			}
		} else {
			$uuids[] = $this->data['uuid'];
		}

		return $uuids;
	}

}