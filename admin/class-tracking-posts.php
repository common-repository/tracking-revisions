<?php
/**
 * Class: TKR_Tracking_Posts
 *
 * @author hien-tech (nickanhem@gmail.com)
 * @since 2019-10-17
 */
class TKR_Tracking_Posts {
	var $pre_post_info;
	
	// construct
	function __construct() {
		$this->pre_post_info = array();
	}

	// init
	function init() {
		add_action('pre_post_update', array($this, 'get_pre_post_update'), 1, 2);

		add_action('save_post', array($this, 'tracking_posts'), 10, 3);
	}

	// get: The value before updating
	function get_pre_post_update($post_id, $post_data) {
		// if this is just a revision, do nothing.
		if (wp_is_post_revision($post_id)) {
			return;
		}

		$post_info = get_post($post_id);
		if (empty($post_info)) {
			return;
		}

		// save the value before changing
		$this->pre_post_info['title'] = $post_info->post_title;

		$author_info = get_userdata(intval($post_info->post_author));
		if ($author_info) {
			$this->pre_post_info['author_id'] = $author_info->ID;
			$this->pre_post_info['author_name'] = $author_info->display_name;
			$this->pre_post_info['author_email'] = $author_info->user_email;
		} else {
			$this->pre_post_info['author_id'] = 0;
			$this->pre_post_info['author_name'] = 'Anonymous';
			$this->pre_post_info['author_email'] = 'warning@trackingrevisions.com';
		}

		$this->pre_post_info['post_modified'] = $post_info->post_modified;

		return;
	}

	// action: Tracking posts
	function tracking_posts($post_id, $post, $update) {
		// if this is just a revision, do nothing.
		if (wp_is_post_revision($post_id)) {
			return;
		}

		// push_larksuite_apps
		$larksuite_integration = get_site_option('tkr_larksuite_integration', 1);
		if ($larksuite_integration) $this->larksuite_apps_integration($post_id, $post, $update);

		// push_slack_apps
		$slack_integration = get_site_option('tkr_slack_integration', 1);
		if ($slack_integration) $this->slack_apps_integration($post_id, $post, $update);

		// push_bitrix24_apps
		$bitrix24_integration = get_site_option('tkr_bitrix24_integration', 1);
		if ($bitrix24_integration) $this->bitrix24_apps_integration($post_id, $post, $update);

		return;
	}

	// check: larksuite apps integration
	function larksuite_apps_integration($post_id, $post, $update) {
		// init
		$subject = $message = '';
		$larksuite_slug = get_site_option('tkr_larksuite_slug', '');

		// get information
		$message .= ">>Event: tracking.revisions[BR]";
		$message .= ">>" . __("Post Title:", "tracking-revisions") . " " . $post->post_title . "[BR]";

		$message .= ">>" . __("Post URL:", "tracking-revisions") . " " . get_permalink($post->ID) . "[BR]";
		$message .= ">>" . __("Post Type:", "tracking-revisions") . " " . $post->post_type . "[BR]";

		$current_user = wp_get_current_user();

		// if the article is updated
		if ($update) {
			$pre_post_info = $this->pre_post_info;

			// the interval between two edits. If it goes past the above time, notify me on the Slack.
			$notification = get_site_option('tkr_notification', 15);
			$mins = (time() - strtotime($pre_post_info['post_modified'])) / 60;
			if (intval($mins) < intval($notification)) {
				return;
			}
			
			$url_list = wp_extract_urls($post->post_content);
			$linkout_url = array_diff($url_list, [get_permalink($post->ID)]);
			if (count($linkout_url) > 0) {
				$subject = __("A post has been updated on your website. But linkout (please check now)", "tracking-revisions");

				$message .= ">>" . __("Linkout:", "tracking-revisions") . "[BR]";
				foreach ($linkout_url as $key => $value) {
					$message .= ">>" . '- ' . $value . "[BR]";
				}
			} else {
				$subject = __("A post has been updated on your website", "tracking-revisions");
			}

			$message .= ">>" . __("Updated By:", "tracking-revisions") . " " . $current_user->display_name . " <" . $current_user->user_email . ">[BR]";
			$message .= ">>" . __("Updated At:", "tracking-revisions") . " " . date("F j, Y, g:i a") . "[BR]";
			
			// if different author then mention
			if ($pre_post_info['author_id'] != $current_user->ID) {
				$message .= ">>" . __("Old Author:", "tracking-revisions") . " " . $pre_post_info['author_name'] . " <" . $pre_post_info['author_email'] . ">[BR]";
			}
			
		// if the post is initialized
		} else {
			$subject = __("A post has been added on your website", "tracking-revisions");

			$message .= ">>" . __("Created By:", "tracking-revisions") . " " . $current_user->display_name . " <" . $current_user->user_email . ">[BR]";
			$message .= ">>" . __("Created At:", "tracking-revisions") . " " . date("F j, Y, g:i a") . "[BR]";
		}

		$ip_address = get_the_user_ip();
		$ip_address_list = explode(',', str_replace(' ', '', $ip_address));

		// Mention Admin
		$ip_whitelist = get_site_option('tkr_ip_white_list', '');
		$ip_whitelist_arr = explode(',', $ip_whitelist);

		$mention_admin = '';
		if (count($ip_whitelist_arr) > 1) {
			$diff = array_diff($ip_address_list, $ip_whitelist_arr);

			$mention_admin = (count($diff) == count($ip_address_list)) ? '(Địa chỉ IP ngoài hệ thống Vidi)' : '';
		}

		$message .= ">>" . __("IP Address:", "tracking-revisions") . " " . $ip_address . " " . $mention_admin;

		// push_larksuite_apps
		push_larksuite_apps($larksuite_slug, $subject, $message);
	}

	// check: slack apps integration
	function slack_apps_integration($post_id, $post, $update) {
		// init
		$slack_member_id_str = $subject = $title = $message = '';

		// slack_member_id_str
		$slack_member_id = get_site_option('tkr_slack_member_id', '');
		if (!empty($slack_member_id)) {
			$slack_member_id_arr = explode(',', $slack_member_id);
			if (count($slack_member_id_arr) > 0) {
				foreach ($slack_member_id_arr as $key => $value) {
					$slack_member_id_str .= ' <@' . $value . '> ';
				}
			}
		}

		// get information
		$title = __("Post Title:", "tracking-revisions") . " " . $post->post_title . " \n";

		$message .= __("Post URL:", "tracking-revisions") . " " . get_permalink($post->ID) . " \n";
		$message .= __("Post Type:", "tracking-revisions") . " " . $post->post_type . " \n";

		$current_user = wp_get_current_user();

		// if the article is updated
		if ($update) {
			$pre_post_info = $this->pre_post_info;

			// the interval between two edits. If it goes past the above time, notify me on the Slack.
			$notification = get_site_option('tkr_notification', 15);
			$mins = (time() - strtotime($pre_post_info['post_modified'])) / 60;
			if (intval($mins) < intval($notification)) {
				return;
			}
			
			$url_list = wp_extract_urls($post->post_content);
			$linkout_url = array_diff($url_list, [get_permalink($post->ID)]);
			if (count($linkout_url) > 0) {
				$subject = __("A post has been updated on your website. But linkout (please check now)", "tracking-revisions");
				$subject .= $slack_member_id_str;

				$message .= __("Linkout:", "tracking-revisions") . " \n";
				foreach ($linkout_url as $key => $value) {
					$message .= '- ' . $value . " \n";
				}
			} else {
				$subject = __("A post has been updated on your website", "tracking-revisions");

				// the interval between two edits. If it goes past the above time, mention me on the Slack.
				$mention = get_site_option('tkr_mention', 15);
				$hours = (time() - strtotime($pre_post_info['post_modified'])) / 3600;
				if (intval($hours) > intval($mention)) {
					$subject .= $slack_member_id_str;
				}
			}

			$message .= __("Updated By:", "tracking-revisions") . " " . $current_user->display_name . " <" . $current_user->user_email . "> \n";
			$message .= __("Updated At:", "tracking-revisions") . " " . date("F j, Y, g:i a") . " \n";
			
			// if different author then mention
			if ($pre_post_info['author_id'] != $current_user->ID) {
				$message .= __("Old Author:", "tracking-revisions") . " " . $pre_post_info['author_name'] . " <" . $pre_post_info['author_email'] . "> \n";
			}
			
		// if the post is initialized
		} else {
			$subject = __("A post has been added on your website", "tracking-revisions") . " " . $slack_member_id_str;

			$message .= __("Created By:", "tracking-revisions") . " " . $current_user->display_name . " <" . $current_user->user_email . "> \n";
			$message .= __("Created At:", "tracking-revisions") . " " . date("F j, Y, g:i a") . " \n";
		}

		$ip_address = get_the_user_ip();
		$message .= __("IP Address:", "tracking-revisions") . " " . $ip_address;

		// push_slack_apps
		push_slack_apps($subject, $title, $message);
	}

	// check: bitrix24 apps integration
	function bitrix24_apps_integration($post_id, $post, $update) {
		// init
		$subject = $title = $message = '';
		$bitrix24_member_id = get_site_option('tkr_bitrix24_member_id', '');
		$bitrix24_chat_id = get_site_option('tkr_bitrix24_chat_id', '');

		// get information
		$message .= ">>" . __("Post Title:", "tracking-revisions") . " " . $post->post_title . "[BR]";

		$message .= ">>" . __("Post URL:", "tracking-revisions") . " " . get_permalink($post->ID) . "[BR]";
		$message .= ">>" . __("Post Type:", "tracking-revisions") . " " . $post->post_type . "[BR]";

		$current_user = wp_get_current_user();

		// if the article is updated
		if ($update) {
			$pre_post_info = $this->pre_post_info;

			// the interval between two edits. If it goes past the above time, notify me on the Slack.
			$notification = get_site_option('tkr_notification', 15);
			$mins = (time() - strtotime($pre_post_info['post_modified'])) / 60;
			if (intval($mins) < intval($notification)) {
				return;
			}
			
			$url_list = wp_extract_urls($post->post_content);
			$linkout_url = array_diff($url_list, [get_permalink($post->ID)]);
			if (count($linkout_url) > 0) {
				$subject = __("A post has been updated on your website. But linkout (please check now)", "tracking-revisions");
				$subject .= $bitrix24_member_id;

				$message .= ">>" . __("Linkout:", "tracking-revisions") . "[BR]";
				foreach ($linkout_url as $key => $value) {
					$message .= ">>" . '- ' . $value . "[BR]";
				}
			} else {
				$subject = __("A post has been updated on your website", "tracking-revisions");

				// the interval between two edits. If it goes past the above time, mention me on the Slack.
				$mention = get_site_option('tkr_mention', 15);
				$hours = (time() - strtotime($pre_post_info['post_modified'])) / 3600;
				if (intval($hours) > intval($mention)) {
					$subject .= $bitrix24_member_id;
				}
			}

			$message .= ">>" . __("Updated By:", "tracking-revisions") . " " . $current_user->display_name . " <" . $current_user->user_email . ">[BR]";
			$message .= ">>" . __("Updated At:", "tracking-revisions") . " " . date("F j, Y, g:i a") . "[BR]";
			
			// if different author then mention
			if ($pre_post_info['author_id'] != $current_user->ID) {
				$message .= ">>" . __("Old Author:", "tracking-revisions") . " " . $pre_post_info['author_name'] . " <" . $pre_post_info['author_email'] . ">[BR]";
			}
			
		// if the post is initialized
		} else {
			$subject = __("A post has been added on your website", "tracking-revisions") . " " . $bitrix24_member_id;

			$message .= ">>" . __("Created By:", "tracking-revisions") . " " . $current_user->display_name . " <" . $current_user->user_email . ">[BR]";
			$message .= ">>" . __("Created At:", "tracking-revisions") . " " . date("F j, Y, g:i a") . "[BR]";
		}

		$ip_address = get_the_user_ip();
		$message .= ">>" . __("IP Address:", "tracking-revisions") . " " . $ip_address;

		// push_bitrix24_apps
		push_bitrix24_apps($bitrix24_chat_id, $subject, $message);
	}
}