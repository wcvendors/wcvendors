<?php
namespace WCVendors;

/**
 * Class VendorHandler
 * Handle Vendor Table Action
 */

if (!defined('ABSPATH')) {
	exit;
}

use WP_User;

class VendorHandler {

	public function __construct() {

	}
	/**
	 * Init all hook for vendor action
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function init_hooks() {
		add_action('wcvendors_throw_message', array($this, 'throw_message'), 10, 2);
		add_action('wp_ajax_enable_vendor', array($this, 'toogle_enable'));
		add_action('wcvendors_approve', array($this, 'approve'), 10, 1);
		add_action('wcvendors_deny', array($this, 'deny'), 10, 1);
		add_action('wcvendors_delete', array($this, 'delete'), 10, 1);
		add_action('wcvendors_bulkdeny_vendor', array($this, 'bulk_deny'), 10, 1);
		add_action('wcvendors_bulkapprove_vendor', array($this, 'bulk_approve'), 10, 1);
		add_action('wcvendors_bulkdisable_vendor', array($this, 'bulk_disable'), 10, 1);
		add_action('wcvendors_bulkenable_vendor', array($this, 'bulk_enable'), 10, 1);
		add_action('wcvendors_bulkdelete_vendor', array($this, 'bulk_delete'), 10, 1);
	}

	public function test($vendor_id) {
		echo $vendor_id;
	}
	/**
	 * Delete single vendor
	 *
	 * @param int $vendor_id
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function delete($vendor_id) {
		if ($this->user_id_exists($vendor_id)) {
			wp_delete_user($vendor_id, 1);
		}
		return true;
	}

	/**
	 * Handle bulk delete vendor
	 *
	 * @param array $vendor_ids
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function bulk_delete($vendor_ids) {
		foreach ($vendor_ids as $vendor_id) {
			$this->delete($vendor_id);
		}
		$this->throw_message(__('Deleted all selected user', 'wcv-vendors'), 'success');
		return true;
	}

	/**
	 * Deny single vendor
	 *
	 * @param int $vendor_id
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function deny($vendor_id) {
		$user = new WP_User($vendor_id);
		$user_role = $user->roles;
		if (in_array('pending_vendor', $user_role)) {
			$user->remove_role('pending_vendor');
			$user->set_role('subscriber');
			$this->throw_message(__(sprintf('Denied %s',$user->user_nicename ), 'wcv-vendors'), 'success');
			return true;
		}

		$this->throw_message(__(sprintf('Cannot deny %s',$user->user_nicename ), 'wcv-vendors'), 'error');
		return false;
	}

	/**
	 * Handle bulk delete vendor
	 *
	 * @param array $vendor_ids
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function bulk_deny($vendor_ids) {
		$users_args = array(
			'include' => $vendor_ids,
		);
		$users = get_users($users_args);

		foreach ($users as $user) {
			if (in_array('pending_vendor', (array) $user->roles)) {
				$user->remove_role('pending_vendor');
				$user->set_role('subscriber');

			}
		}
		$this->throw_message(__('Denied all selected vendor', 'wcv-vendors'), 'success');
		return true;
	}
	/**
	 * Approve single vendor
	 *
	 * @param int $vendor_id
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function approve($vendor_id) {

		$user = new WP_User($vendor_id);
		$user_role = $user->roles;

		if (in_array('pending_vendor', $user_role)) {
			$user->remove_role('pending_vendor');
			$user->set_role('vendor');
			$this->throw_message(__(sprintf('Approved %s',$user->user_nicename ), 'wcv-vendors'), 'success');
			return true;
		}

		$this->throw_message(__(sprintf('Cannot approve %s',$user->user_nicename ), 'wcv-vendors'), 'error');
		return false;

	}
	/**
	 * Hanlde bulk approve vendor
	 *
	 * @param array $vendor_ids
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function bulk_approve($vendor_ids) {

		$users_args = array(
			'include' => $vendor_ids,
		);
		$users = get_users($users_args);

		foreach ($users as $user) {
			if (in_array('pending_vendor', (array) $user->roles)) {
				$user->remove_role('pending_vendor');
				$user->set_role('vendor');

			}
		}
		$this->throw_message(__('Approved all selected vendor', 'wcv-vendors'), 'success');
		return true;

	}

	/**
	 * Disable single vendor
	 *
	 * @param int $vendor_id
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function disable($vendor_id) {

		$is_vendor_enabled = get_user_meta(absint($vendor_id), 'is_vendor_enabled', true);

		if ($is_vendor_enabled) {
			update_user_meta(absint($vendor_id), 'is_vendor_enabled', false);
			return true;
		}

	}

	/**
	 * Handle bulk disable vendor
	 *
	 * @param array $vendor_ids
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function bulk_disable($vendor_ids) {

		foreach ($vendor_ids as $id) {
			$this->disable($id);
		}
		$this->throw_message(__('Disable all selected vendor', 'wcv-vendors'), 'success');
		return true;

	}

	/**
	 * Disable or enable vendor ajax
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function toogle_enable() {
		if (!check_ajax_referer('wcv_toogle_vendor_nonce', 'security', false)) {
			wp_send_json_error('Invalid security token sent.');
			wp_die();
		}

		$vendor_id = isset($_REQUEST['vendor_id']) ? absint($_REQUEST['vendor_id']) : '';
		$is_vendor_enabled = get_user_meta($vendor_id, 'is_vendor_enabled', true);

		$result = false;

		if (!$is_vendor_enabled) {
			$result = $this->enable($vendor_id);
		} else {
			$result = $this->disable($vendor_id);
		}

		echo $result;

		wp_die();
	}

	/**
	 * Enable single vendor
	 *
	 * @param string $vendor_id
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function enable($vendor_id) {
		$is_vendor_enabled = get_user_meta(absint($vendor_id), 'is_vendor_enabled', true);

		if (!$is_vendor_enabled) {
			update_user_meta(absint($vendor_id), 'is_vendor_enabled', true);
		}
	}
	/**
	 * Handle bulk enable vendor
	 *
	 * @param array $vendor_ids
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function bulk_enable($vendor_ids) {
		foreach ($vendor_ids as $id) {
			$this->enable($id);
		}
		$this->throw_message(__('Enable all selected vendor', 'wcv-vendors'), 'success');
		return true;
	}

	/**
	 * Display message
	 *
	 * @param string $message
	 * @param string $type type of wordpress notice
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return string
	 */

	public function throw_message($message = '', $type = 'success') {
		echo sprintf('<div class="notice notice-%s is-dismissible"><p><strong>%s</strong></p></div>', $type, $message);
	}

	/**
	 * Check if user exists
	 *
	 * @param int $user_id
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */

	public function user_id_exists($user_id) {
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->users WHERE ID = %d", $user_id));
		return empty($count) || 1 > $count ? false : true;
	}

}
