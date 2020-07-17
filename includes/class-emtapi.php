<?php

class EMTApi
{
	
	protected $apiKey;
	protected $tracker_url = 'https://emailtools.ru/tracker/'; 
	protected $timeout;
	protected $origin;

	public function __construct($apiKey, $timeout = 3)
	{
		
		$this->apiKey = $apiKey;
		$this->timeout = $timeout;
		$this->origin = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://' . $_SERVER ['HTTP_HOST'];
	}

	public function sendOperation($task, $param) {
		
		if(!isset($_COOKIE['emt_uuuid']) || empty($_COOKIE['emt_uuuid'])) return 'error uuuid cookie';
		
		$param['uuuid'] = sanitize_text_field($_COOKIE['emt_uuuid']);
		$param['client_id'] = sanitize_text_field($this->apiKey);
		$param['task'] = sanitize_text_field($task);
		
		if(empty($param['uuuid'])) return 'error uuuid cookie';
		if(empty($param['client_id'])) return 'error api key';
		if(empty($param['task'])) return 'error task name';

		$post_args = array(
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept' => '*/*',
				'Origin' => $this->origin
			),
			'body' => $param,
			'timeout' => $this->timeout,
		);
		
		$response = wp_remote_post( $this->tracker_url, $post_args );
		$response_body = wp_remote_retrieve_body( $response );
		
		return $response_body;
	}
	
}