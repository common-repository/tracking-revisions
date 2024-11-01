<?php
/**
 * Class: TKR_Admin
 *
 * @author hien-tech (nickanhem@gmail.com)
 * @since 2019-10-17
 */
class TKR_Admin {
	// init
	function init() {
		// settings: Wordpress multisite
		if (is_multisite()) {
			add_action('wpmu_options', array($this, 'wpmu_options'));
			add_action('update_wpmu_options', array($this, 'update_wpmu_options'));
		}

		add_action('admin_init', array($this, 'admin_init'));

		add_action('admin_bar_menu', array($this, 'add_bar_menu'), 999999);

		add_action('admin_enqueue_scripts', array($this, 'add_styles'), 999);
	}

	// add block settings: Wordpress multisite
	function wpmu_options() {
		$out = '';

		$out .= '<h2>Tracking Revisions</h2>';

		$out .= '<div id="tkr-settings">';
		$out .= sprintf(__('Need help? Try the <a href="%1$s" target="_blank">support forum</a>. This plugin is kindly brought to you by <a href="%2$s" target="_blank">Hien Nguyen Duy</a>', 'tracking-revisions'), 'http://wordpress.org/support/plugin/tracking-revisions/', 'https://www.linkedin.com/in/hiennguyenduy/');
		$out .= ' (' . __('please donate me', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://www.paypal.com/paypalme/hiennguyenduy') . '))';
		$out .= '</div>';

		$out .= '<table class="form-table"><tbody>';
			// Larksuite integration
			$larksuite_integration = get_site_option('tkr_larksuite_integration', 1);
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_larksuite_integration">' . __('Larksuite Integration', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input type="checkbox" id="tkr_larksuite_integration" name="tkr_larksuite_integration" value="1" ' . checked(1, $larksuite_integration, false) . '/>';
					$out .= '<label for="tkr_larksuite_integration">' . __('Yes, I do love it.', 'tracking-revisions') . '</label>';
				$out .= '</td>';
			$out .= '</tr>';

			$larksuite_webhook_url = get_site_option('tkr_larksuite_webhook_url', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_larksuite_webhook_url">' . __('Webhook URL', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input id="tkr_larksuite_webhook_url" class="regular-text ltr" type="text" name="tkr_larksuite_webhook_url" value="' . $larksuite_webhook_url . '">';
				$out .= '</td>';
			$out .= '</tr>';

			$larksuite_header_signing_key = get_site_option('tkr_larksuite_header_signing_key', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_larksuite_header_signing_key">' . __('Header Signing Key', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input id="tkr_larksuite_header_signing_key" class="regular-text ltr" type="text" name="tkr_larksuite_header_signing_key" value="' . $larksuite_header_signing_key . '">';
				$out .= '</td>';
			$out .= '</tr>';

			$larksuite_signing_key_value = get_site_option('tkr_larksuite_signing_key_value', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_larksuite_signing_key_value">' . __('Signing Key Value', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input id="tkr_larksuite_signing_key_value" class="regular-text ltr" type="text" name="tkr_larksuite_signing_key_value" value="' . $larksuite_signing_key_value . '">';
				$out .= '</td>';
			$out .= '</tr>';

			$larksuite_slug = get_site_option('tkr_larksuite_slug', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_larksuite_slug">' . __('Slug', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input id="tkr_larksuite_slug" class="regular-text ltr" type="text" name="tkr_larksuite_slug" value="' . $larksuite_slug . '">';
				$out .= '</td>';
			$out .= '</tr>';
			
			// Slack integration
			$slack_integration = get_site_option('tkr_slack_integration', 1);
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_slack_integration">' . __('Slack Integration', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input type="checkbox" id="tkr_slack_integration" name="tkr_slack_integration" value="1" ' . checked(1, $slack_integration, false) . '/>';
					$out .= '<label for="tkr_slack_integration">' . __('Yes, I do love it.', 'tracking-revisions') . '</label>';
				$out .= '</td>';
			$out .= '</tr>';

			$slack_webhook_url = get_site_option('tkr_slack_webhook_url', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_slack_webhook_url">' . __('Webhook URL', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<code>https://hooks.slack.com/services/</code> <input id="tkr_slack_webhook_url" class="atmcotr-regular-text ltr" type="text" name="tkr_slack_webhook_url" value="' . $slack_webhook_url . '"> <code>/</code>';
					$out .= '<p class="description">' . __('This is sensitive information. You should not share it with many people.', 'tracking-revisions') . ' ' . __('Try do it', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://api.slack.com/messaging/webhooks') . ') </p>';
				$out .= '</td>';
			$out .= '</tr>';

			$slack_member_id = get_site_option('tkr_slack_member_id', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_slack_member_id">' . __('Member ID', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input id="tkr_slack_member_id" class="regular-text ltr" type="text" name="tkr_slack_member_id" value="' . $slack_member_id . '">';
					$out .= '<p class="description">' . __('Use commas to add more data', 'tracking-revisions') . ' (' . __('example:', 'tracking-revisions') . ' UASTL2E06,UASTL2E08). ' . __('Try do it', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://slack.com/intl/en-vn/help/articles/360003827751-Create-a-link-to-a-members-profile-') . ')</p>';
				$out .= '</td>';
			$out .= '</tr>';

			// Bitrix24 integration
			$bitrix24_integration = get_site_option('tkr_bitrix24_integration', 1);
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_bitrix24_integration">' . __('Bitrix24 Integration', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input type="checkbox" id="tkr_bitrix24_integration" name="tkr_bitrix24_integration" value="1" ' . checked(1, $bitrix24_integration, false) . '/>';
					$out .= '<label for="tkr_bitrix24_integration">' . __('Yes, I do love it.', 'tracking-revisions') . '</label>';
				$out .= '</td>';
			$out .= '</tr>';

			$bitrix24_webhook_url = get_site_option('tkr_bitrix24_webhook_url', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_bitrix24_webhook_url">' . __('Webhook URL', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input id="tkr_bitrix24_webhook_url" class="atmcotr-regular-text ltr" type="text" name="tkr_bitrix24_webhook_url" value="' . $bitrix24_webhook_url . '">';
					$out .= '<p class="description">' . __('This is sensitive information. You should not share it with many people.', 'tracking-revisions') . ' ' . __('Try do it', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://training.bitrix24.com/rest_help/') . ') </p>';
				$out .= '</td>';
			$out .= '</tr>';

			$bitrix24_chat_id = get_site_option('tkr_bitrix24_chat_id', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_bitrix24_chat_id">' . __('Chat ID', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input id="tkr_bitrix24_chat_id" class="regular-text ltr" type="text" name="tkr_bitrix24_chat_id" value="' . $bitrix24_chat_id . '">';
				$out .= '</td>';
			$out .= '</tr>';

			$bitrix24_member_id = get_site_option('tkr_bitrix24_member_id', '');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_bitrix24_member_id">' . __('Member ID', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<input id="tkr_bitrix24_member_id" class="regular-text ltr" type="text" name="tkr_bitrix24_member_id" value="' . $bitrix24_member_id . '">';
					$out .= '<p class="description">' . __('Use commas to add more data', 'tracking-revisions') . ' (' . __('example:', 'tracking-revisions') . ' [USER=282]Hiển Nguyễn Duy[/USER], [USER=727]Jarvis Nguyễn[/USER]). ' . __('Try do it', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://training.bitrix24.com/rest_help/') . ')</p>';
				$out .= '</td>';
			$out .= '</tr>';
			
			// Config
			$slack_mention = get_site_option('tkr_mention', '2');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_mention">' . __('Mention', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<code>' . __('after', 'tracking-revisions') . '</code> <input id="tkr_mention" class="small-text" type="number" min="1" default="1" name="tkr_mention" value="' . $slack_mention . '"> <code>' . __('hours', 'tracking-revisions') . '</code>';
					$out .= '<p class="description">' . __('The interval between two edits. If it goes past the above time, mention me on the Slack.', 'tracking-revisions') . '</p>';
				$out .= '</td>';
			$out .= '</tr>';

			$slack_notification = get_site_option('tkr_notification', '15');
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_notification">' . __('Notification', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$out .= '<code>' . __('after', 'tracking-revisions') . '</code> <input id="tkr_notification" class="small-text" type="number" min="1" default="1" name="tkr_notification" value="' . $slack_notification . '"> <code>' . __('minutes', 'tracking-revisions') . '</code>';
					$out .= '<p class="description">' . __('The interval between two edits. If it goes past the above time, notify me on the Slack.', 'tracking-revisions') . '</p>';
				$out .= '</td>';
			$out .= '</tr>';

			$tkr_tracking = get_site_option('tkr_tracking', ['post_page_custom' => 1, 'taxonomy_custom' => 1]);
			$out .= '<tr>';
				$out .= '<th scope="row"><label for="tkr_tracking">' . __('Tracking', 'tracking-revisions') . '</label></th>';
				$out .= '<td>';
					$post_page_custom = (isset($tkr_tracking['post_page_custom'])) ? $tkr_tracking['post_page_custom'] : 0;
					$out .= '<input type="checkbox" id="post_page_custom" name="tkr_tracking[post_page_custom]" value="1" ' . checked(1, $post_page_custom, false) . '/>';
					$out .= '<label for="post_page_custom">' . __('Post / Page / Custom Post Type', 'tracking-revisions') . '</label>';
				$out .= '</td>';
			$out .= '</tr>';
		$out .= '</tbody></table>';

		echo $out;
	}

	// update options: Wordpress multisite
	function update_wpmu_options() {
		if (!empty($_POST) && check_admin_referer('siteoptions') ) {
			// Larksuite integration
			update_site_option('tkr_larksuite_integration', $_POST['tkr_larksuite_integration']);
			update_site_option('tkr_larksuite_webhook_url', $_POST['tkr_larksuite_webhook_url']);
			update_site_option('tkr_larksuite_header_signing_key', $_POST['tkr_larksuite_header_signing_key']);
			update_site_option('tkr_larksuite_signing_key_value', $_POST['tkr_larksuite_signing_key_value']);
			update_site_option('tkr_larksuite_slug', $_POST['tkr_larksuite_slug']);

			// Slack integration
			update_site_option('tkr_slack_integration', $_POST['tkr_slack_integration']);
			update_site_option('tkr_slack_webhook_url', $_POST['tkr_slack_webhook_url']);
			update_site_option('tkr_slack_member_id', $_POST['tkr_slack_member_id']);

			// Bitrix24 integration
			update_site_option('tkr_bitrix24_integration', $_POST['tkr_bitrix24_integration']);
			update_site_option('tkr_bitrix24_webhook_url', $_POST['tkr_bitrix24_webhook_url']);
			update_site_option('tkr_bitrix24_chat_id', $_POST['tkr_bitrix24_chat_id']);
			update_site_option('tkr_bitrix24_member_id', $_POST['tkr_bitrix24_member_id']);

			// Config
			update_site_option('tkr_mention', $_POST['tkr_mention']);
			update_site_option('tkr_notification', $_POST['tkr_notification']);
			update_site_option('tkr_tracking', $_POST['tkr_tracking']);

			flush_rewrite_rules();
		}
	}

	// register: Options
	function admin_init() {
		add_settings_section(
			'tracking-revisions-section',
			'Tracking Revisions',
			array($this, 'tkr_section_desc'),
			'general'
		);

		// Larksuite integration
		add_settings_field(
			'tkr_larksuite_integration',
			'<label for="tkr_larksuite_integration">' . __('Larksuite Integration', 'tracking-revisions') . '</label>',
			array($this, 'tkr_larksuite_integration_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_larksuite_webhook_url',
			'<label for="tkr_larksuite_webhook_url">' . __('Webhook URL', 'tracking-revisions') . '</label>',
			array($this, 'tkr_larksuite_webhook_url_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_larksuite_header_signing_key',
			'<label for="tkr_larksuite_header_signing_key">' . __('Header Signing Key', 'tracking-revisions') . '</label>',
			array($this, 'tkr_larksuite_header_signing_key_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_larksuite_signing_key_value',
			'<label for="tkr_larksuite_signing_key_value">' . __('Signing Key Value', 'tracking-revisions') . '</label>',
			array($this, 'tkr_larksuite_signing_key_value_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_larksuite_slug',
			'<label for="tkr_larksuite_slug">' . __('Slug', 'tracking-revisions') . '</label>',
			array($this, 'tkr_larksuite_slug_input'),
			'general',
			'tracking-revisions-section'
		);

		// Slack integration
		add_settings_field(
			'tkr_slack_integration',
			'<label for="tkr_slack_integration">' . __('Slack Integration', 'tracking-revisions') . '</label>',
			array($this, 'tkr_slack_integration_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_slack_webhook_url',
			'<label for="tkr_slack_webhook_url">' . __('Webhook URL', 'tracking-revisions') . '</label>',
			array($this, 'tkr_slack_webhook_url_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_slack_member_id',
			'<label for="tkr_slack_member_id">' . __('Member ID', 'tracking-revisions') . '</label>',
			array($this, 'tkr_slack_member_id_input'),
			'general',
			'tracking-revisions-section'
		);

		// Bitrix24 integration
		add_settings_field(
			'tkr_bitrix24_integration',
			'<label for="tkr_bitrix24_integration">' . __('Bitrix24 Integration', 'tracking-revisions') . '</label>',
			array($this, 'tkr_bitrix24_integration_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_bitrix24_webhook_url',
			'<label for="tkr_bitrix24_webhook_url">' . __('Webhook URL', 'tracking-revisions') . '</label>',
			array($this, 'tkr_bitrix24_webhook_url_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_bitrix24_chat_id',
			'<label for="tkr_bitrix24_chat_id">' . __('Chat ID', 'tracking-revisions') . '</label>',
			array($this, 'tkr_bitrix24_chat_id_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_bitrix24_member_id',
			'<label for="tkr_bitrix24_member_id">' . __('Member ID', 'tracking-revisions') . '</label>',
			array($this, 'tkr_bitrix24_member_id_input'),
			'general',
			'tracking-revisions-section'
		);

		// Config
		add_settings_field(
			'tkr_mention',
			'<label for="tkr_mention">' . __('Mention', 'tracking-revisions') . '</label>',
			array($this, 'tkr_mention_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_notification',
			'<label for="tkr_notification">' . __('Notification', 'tracking-revisions') . '</label>',
			array($this, 'tkr_notification_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_ip_white_list',
			'<label for="tkr_ip_white_list">' . __('IP white list', 'tracking-revisions') . '</label>',
			array($this, 'tkr_ip_white_list_input'),
			'general',
			'tracking-revisions-section'
		);
		add_settings_field(
			'tkr_tracking',
			'<label for="tkr_tracking">' . __('Tracking', 'tracking-revisions') . '</label>',
			array($this, 'tkr_tracking_input'),
			'general',
			'tracking-revisions-section'
		);

		// register: larksuite
		register_setting('general', 'tkr_larksuite_integration');
		register_setting('general', 'tkr_larksuite_webhook_url');
		register_setting('general', 'tkr_larksuite_header_signing_key');
		register_setting('general', 'tkr_larksuite_signing_key_value');
		register_setting('general', 'tkr_larksuite_slug');

		// register: slack
		register_setting('general', 'tkr_slack_integration');
		register_setting('general', 'tkr_slack_webhook_url');
		register_setting('general', 'tkr_slack_member_id');

		// register: bitrix24
		register_setting('general', 'tkr_bitrix24_integration');
		register_setting('general', 'tkr_bitrix24_webhook_url');
		register_setting('general', 'tkr_bitrix24_chat_id');
		register_setting('general', 'tkr_bitrix24_member_id');

		// register: config
		register_setting('general', 'tkr_mention');
		register_setting('general', 'tkr_notification');
		register_setting('general', 'tkr_ip_white_list');
		register_setting('general', 'tkr_tracking');
	}

	// section: Description
	function tkr_section_desc() {
		$out = '';

		$out .= '<div id="tkr-settings">';

		if (is_super_admin()) {
			$out .= sprintf(__('Need help? Try the <a href="%1$s" target="_blank">support forum</a>. This plugin is kindly brought to you by <a href="%2$s" target="_blank">Hien Nguyen Duy</a>', 'tracking-revisions'), 'http://wordpress.org/support/plugin/tracking-revisions/', 'https://www.linkedin.com/in/hiennguyenduy/');
			$out .= ' (' . __('please donate me', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://www.paypal.com/paypalme/hiennguyenduy') . '))';
		}

		if (is_multisite() && is_super_admin()) {
			$out .= '<p>' . sprintf(__('To set a network wide default, go to <a href="%s">Network Settings</a>.', 'tracking-revisions'), network_admin_url('settings.php#tkr-settings')) . '</p>';
		}

		$out .= '</div>';

		echo $out;
	}

	// input: Larksuite Integration
	function tkr_larksuite_integration_input() {
		$larksuite_integration = get_site_option('tkr_larksuite_integration', 1);

		echo '<input type="checkbox" id="tkr_larksuite_integration" name="tkr_larksuite_integration" value="1" ' . checked(1, $larksuite_integration, false) . '/>';
		echo '<label for="tkr_larksuite_integration">' . __('Yes, I do love it.', 'tracking-revisions') . '</label>';
	}

	// input: Webhook URL
	function tkr_larksuite_webhook_url_input() {
		$larksuite_webhook_url = get_site_option('tkr_larksuite_webhook_url', '');

		echo '<input id="tkr_larksuite_webhook_url" class="regular-text ltr" type="text" name="tkr_larksuite_webhook_url" value="' . $larksuite_webhook_url . '">';
	}

	// input: Larksuite Slug
	function tkr_larksuite_header_signing_key_input() {
		$larksuite_header_signing_key = get_site_option('tkr_larksuite_header_signing_key', '');

		echo '<input id="tkr_larksuite_header_signing_key" class="regular-text ltr" type="text" name="tkr_larksuite_header_signing_key" value="' . $larksuite_header_signing_key . '">';
	}

	// input: Larksuite Slug
	function tkr_larksuite_signing_key_value_input() {
		$larksuite_signing_key_value = get_site_option('tkr_larksuite_signing_key_value', '');

		echo '<input id="tkr_larksuite_signing_key_value" class="regular-text ltr" type="text" name="tkr_larksuite_signing_key_value" value="' . $larksuite_signing_key_value . '">';
	}

	// input: Larksuite Slug
	function tkr_larksuite_slug_input() {
		$larksuite_slug = get_site_option('tkr_larksuite_slug', '');

		echo '<input id="tkr_larksuite_slug" class="regular-text ltr" type="text" name="tkr_larksuite_slug" value="' . $larksuite_slug . '">';
	}

	// input: Slack Integration
	function tkr_slack_integration_input() {
		$slack_integration = get_site_option('tkr_slack_integration', 1);

		echo '<input type="checkbox" id="tkr_slack_integration" name="tkr_slack_integration" value="1" ' . checked(1, $slack_integration, false) . '/>';
		echo '<label for="tkr_slack_integration">' . __('Yes, I do love it.', 'tracking-revisions') . '</label>';
	}

	// input: Slack Webhook URL
	function tkr_slack_webhook_url_input() {
		$slack_webhook_url = get_site_option('tkr_slack_webhook_url', '');

		echo '<code>https://hooks.slack.com/services/</code> <input id="tkr_slack_webhook_url" class="atmcotr-regular-text ltr" type="text" name="tkr_slack_webhook_url" value="' . $slack_webhook_url . '"> <code>/</code>';
		echo '<p class="description">' . __('This is sensitive information. You should not share it with many people.', 'tracking-revisions') . ' ' . __('Try do it', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://api.slack.com/messaging/webhooks') . ') </p>';
	}

	// input: Slack Member ID
	function tkr_slack_member_id_input() {
		$slack_member_id = get_site_option('tkr_slack_member_id', '');

		echo '<input id="tkr_slack_member_id" class="regular-text ltr" type="text" name="tkr_slack_member_id" value="' . $slack_member_id . '">';
		echo '<p class="description">' . __('Use commas to add more data', 'tracking-revisions') . ' (' . __('example:', 'tracking-revisions') . ' UASTL2E06,UASTL2E08). ' . __('Try do it', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://slack.com/intl/en-vn/help/articles/360003827751-Create-a-link-to-a-members-profile-') . ')</p>';
	}

	// input: Bitrix24 Integration
	function tkr_bitrix24_integration_input() {
		$bitrix24_integration = get_site_option('tkr_bitrix24_integration', 1);

		echo '<input type="checkbox" id="tkr_bitrix24_integration" name="tkr_bitrix24_integration" value="1" ' . checked(1, $bitrix24_integration, false) . '/>';
		echo '<label for="tkr_bitrix24_integration">' . __('Yes, I do love it.', 'tracking-revisions') . '</label>';
	}

	// input: Bitrix24 Webhook URL
	function tkr_bitrix24_webhook_url_input() {
		$bitrix24_webhook_url = get_site_option('tkr_bitrix24_webhook_url', '');

		echo '<input id="tkr_bitrix24_webhook_url" class="atmcotr-regular-text ltr" type="text" name="tkr_bitrix24_webhook_url" value="' . $bitrix24_webhook_url . '">';
		echo '<p class="description">' . __('This is sensitive information. You should not share it with many people.', 'tracking-revisions') . ' ' . __('Try do it', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://training.bitrix24.com/rest_help/') . ') </p>';
	}

	// input: Bitrix24 Chat ID
	function tkr_bitrix24_chat_id_input() {
		$bitrix24_chat_id = get_site_option('tkr_bitrix24_chat_id', '');

		echo '<input id="tkr_bitrix24_chat_id" class="regular-text ltr" type="text" name="tkr_bitrix24_chat_id" value="' . $bitrix24_chat_id . '">';
	}

	// input: Bitrix24 Member ID
	function tkr_bitrix24_member_id_input() {
		$bitrix24_member_id = get_site_option('tkr_bitrix24_member_id', '');

		echo '<input id="tkr_bitrix24_member_id" class="regular-text ltr" type="text" name="tkr_bitrix24_member_id" value="' . $bitrix24_member_id . '">';
		echo '<p class="description">' . __('Use commas to add more data', 'tracking-revisions') . ' (' . __('example:', 'tracking-revisions') . ' [USER=282]Hiển Nguyễn Duy[/USER], [USER=727]Jarvis Nguyễn[/USER]). ' . __('Try do it', 'tracking-revisions') . ' (' . sprintf(__('<a href="%1$s" target="_blank">CLICK HERE</a>', 'tracking-revisions'), 'https://training.bitrix24.com/rest_help/') . ')</p>';
	}

	// input: Mention me (after hours)
	function tkr_mention_input() {
		$slack_mention = get_site_option('tkr_mention', '4');

		echo '<code>' . __('after', 'tracking-revisions') . '</code> <input id="tkr_mention" class="small-text" type="number" min="1" default="4" name="tkr_mention" value="' . $slack_mention . '"> <code>' . __('hours', 'tracking-revisions') . '</code>';
		echo '<p class="description">' . __('The interval between two edits. If it goes past the above time, mention me on the Slack.', 'tracking-revisions') . '</p>';
	}

	// input: Notif me (after minutes)
	function tkr_notification_input() {
		$slack_notification = get_site_option('tkr_notification', '30');

		echo '<code>' . __('after', 'tracking-revisions') . '</code> <input id="tkr_notification" class="small-text" type="number" min="1" default="30" name="tkr_notification" value="' . $slack_notification . '"> <code>' . __('minutes', 'tracking-revisions') . '</code>';
		echo '<p class="description">' . __('The interval between two edits. If it goes past the above time, notify me on the Slack.', 'tracking-revisions') . '</p>';
	}

	// input: IP white list
	function tkr_ip_white_list_input() {
		$ip_white_list = get_site_option('tkr_ip_white_list', '');

		echo '<input id="tkr_ip_white_list" class="regular-text ltr" type="text" name="tkr_ip_white_list" value="' . $ip_white_list . '">';
	}

	// input: Tracking
	function tkr_tracking_input() {
		$tkr_tracking = get_site_option('tkr_tracking', ['post_page_custom' => 1, 'taxonomy_custom' => 1]);

		$post_page_custom = (isset($tkr_tracking['post_page_custom'])) ? $tkr_tracking['post_page_custom'] : 0;
		echo '<input type="checkbox" id="post_page_custom" name="tkr_tracking[post_page_custom]" value="1" ' . checked(1, $post_page_custom, false) . '/>';
		echo '<label for="post_page_custom">' . __('Post / Page / Custom Post Type', 'tracking-revisions') . '</label>';

		// $taxonomy_custom = (isset($tkr_tracking['taxonomy_custom'])) ? $tkr_tracking['taxonomy_custom'] : 0;
		// echo '<br/><br/>';
		// echo '<input type="checkbox" id="taxonomy_custom" name="tkr_tracking[taxonomy_custom]" value="1" ' . checked(1, $taxonomy_custom, false) . '/>';
		// echo '<label for="taxonomy_custom">' . __('Taxonomy / Custom Taxonomy', 'tracking-revisions') . '</label>';
	}

	// register: Menu top bar
	function add_bar_menu(\WP_Admin_Bar $bar) {
		$bar_menu_href = (is_multisite()) ? network_admin_url('settings.php#tkr-settings') : admin_url('options-general.php#tkr-settings');
		$bar->add_menu(array(
			'id'        => 'atmcowpse',
			'title'     => '<span class="ab-icon"></span><span class="ab-label">Tracking Revisions</span>',
			'href'      => $bar_menu_href
		));
	}

	// register: Styles
	function add_styles() {
		wp_enqueue_style(TKR_PLUGIN . '-styles', TKR_URL . '/assets/css/styles.css', false, TKR_VERSION, 'all');
	}
}