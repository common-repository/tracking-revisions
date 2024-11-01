<?php
// get: IP Address
function get_the_user_ip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		// check ip from share internet
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		// to check ip is pass from proxy
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}

	return $ip_address;
}

// push: The information to the Slack apps
function push_slack_apps($subject = '', $title = '', $message = '') {
	// get webhook url
	$slack_webhook_url = get_site_option( 'tkr_slack_webhook_url', 'zzz' );
	if ($slack_webhook_url == 'zzz' || strlen($slack_webhook_url) < 32 || empty($subject) || empty($title) || empty($message)) {
		return false;
	}

	// nội dung đẩy lên slack
	$notif_str = json_encode(array(
		'attachments' => array(
			array(
				'fallback' => $subject,
				'color' => '#36a64f',
				'pretext' => $subject,
				'title' => $title,
				'text' => $message
			)
		)
	));

	// curl lên slack apps
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://hooks.slack.com/services/' . $slack_webhook_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $notif_str,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: text/plain'
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);

	return true;
}

// push: The information to the Bitrix24 apps
function push_bitrix24_apps($chat_id = '', $subject = '', $message = '') {
	// get webhook url
	$bitrix24_webhook_url = get_site_option( 'tkr_bitrix24_webhook_url', 'zzz' );
	if ($bitrix24_webhook_url == 'zzz' || strlen($bitrix24_webhook_url) < 32 || empty($subject) || empty($chat_id) || empty($message)) {
		return false;
	}

	// nội dung đẩy lên slack
	$notif_str = json_encode(array(
		'chat_id' => $chat_id,
		'subject' => $subject,
		'message' => $message
	));

	// curl lên slack apps
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $bitrix24_webhook_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $notif_str,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);

	return true;
}

// push: The information to the Bitrix24 apps
function push_larksuite_apps($slug = '', $subject = '', $message = '') {
	// get webhook url
	$larksuite_webhook_url = get_site_option( 'tkr_larksuite_webhook_url', 'zzz' );
	$larksuite_header_signing_key = get_site_option( 'tkr_larksuite_header_signing_key', 'zzz' );
	$larksuite_signing_key_value = get_site_option( 'tkr_larksuite_signing_key_value', 'zzz' );

	if ($larksuite_webhook_url == 'zzz' || strlen($larksuite_webhook_url) < 32 || $larksuite_header_signing_key == 'zzz' || $larksuite_signing_key_value == 'zzz' || empty($slug) || empty($subject) || empty($message)) {
		return false;
	}

	// nội dung đẩy lên slack2
	$notif_str = json_encode(array(
		'slug' => $slug,
		'subject' => base64_encode($subject),
		'message' => base64_encode($message)
	));

	// curl lên slack apps
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $larksuite_webhook_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $notif_str,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			$larksuite_header_signing_key . ': ' . $larksuite_signing_key_value
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);

	return true;
}