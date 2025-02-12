<?php

namespace Smartemailing;

class Api {

	protected $apiKey = null;
	protected $username = null;

	private $apiUrl = "https://app.smartemailing.cz/api/v3/";

	public function __construct( $apiKey = null, $username = null ) {
		$this->apiKey   = $apiKey;
		$this->username = $username;
	}


	public function make_request( $path, $data = array(), $method = 'POST' ) {
		$args = array(
			'method'  => $method,
			'headers' => array(
				'accept'        => 'application/json',
				'content-type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->apiKey )
			)
		);

		if ( ! empty( $data ) ) {
			$data["updateEnabled"] = false;
			$args['body']          = json_encode( $data );
		}

		$apiUrl = $this->apiUrl . $path;

		if ( $method == 'POST' ) {
			$response = wp_remote_post( $apiUrl, $args );
		} else if ( $method == 'GET' ) {
			$response = wp_remote_get( $apiUrl, $args );
		} else if ( $method == 'PUT' ) {
			$response = wp_remote_request( $apiUrl, $args );
		} else {
			return ( new \WP_Error( 423, 'Request method could not be found' ) );
		}

		/* If WP_Error, die. Otherwise, return decoded JSON. */
		if ( is_wp_error( $response ) ) {
			return ( new \WP_Error( 423, $response->get_error_message() ) );
		}

		return json_decode( $response['body'], true );
	}

	public function auth_test() {
		return $this->make_request( 'check-credentials/', [], 'GET' );
	}

	public function getLists( $page = 1 ) {
		$limit  = 50;
		$offset = ( $page - 1 ) * $limit;

		$lists = $this->make_request( 'contactlists?limit=' . $limit . '&offset=' . $offset, [], 'GET' );

		if ( is_wp_error( $lists ) || empty( $lists['data'] ) ) {
			return [];
		}

		if ( $page == 1 && $lists['meta']['total_count'] > $limit ) {
			$nextLists = $this->getLists( 2 );

			return array_merge( $nextLists, $lists['data'] );
		}

		return $lists['data'];
	}

	public function attributes() {
		$attributes = $this->make_request( 'customfields', [], 'GET' );

		if ( ! empty( $lists['error'] ) ) {
			return [];
		}

		return $attributes;
	}

	public function addContact( $data ) {

		$request = [
			'data'     => [ $data ],
			"settings" => [
				"update" => true,
			]
		];

		$response = $this->make_request( 'import', $request, 'POST' );

		if ( ! empty( $response['error'] ) ) {
			return new \WP_Error( 'api_error', $response['message'] );
		}

		return $response;
	}
}
