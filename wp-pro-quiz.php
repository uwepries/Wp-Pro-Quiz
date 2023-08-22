<?php
/**
 * Wp-Pro-Quiz
 *
 * @author Julius Fischer
 * @copyright 2012-2020 Julius Fischer (IT-Gecko.de)
 * @license GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Wp-Pro-Quiz
 * Plugin URI: http://wordpress.org/extend/plugins/wp-pro-quiz
 * Description: A powerful and beautiful quiz plugin for WordPress.
 * Version: 0.37
 * Requires at least: 4.6
 * Requires PHP: 5.6
 * Author: Julius Fischer
 * Author URI: http://www.it-gecko.de
 * Text Domain: wp-pro-quiz
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define('WPPROQUIZ_VERSION', '0.37');

define('WPPROQUIZ_DEV', false);

define('WPPROQUIZ_PATH', dirname(__FILE__));
define('WPPROQUIZ_URL', plugins_url('', __FILE__));
define('WPPROQUIZ_FILE', __FILE__);
define('WPPROQUIZ_PPATH', dirname(plugin_basename(__FILE__)));
define('WPPROQUIZ_PLUGIN_PATH', WPPROQUIZ_PATH . '/plugin');

$uploadDir = wp_upload_dir();

define('WPPROQUIZ_CAPTCHA_DIR', $uploadDir['basedir'] . '/wp_pro_quiz_captcha');
define('WPPROQUIZ_CAPTCHA_URL', $uploadDir['baseurl'] . '/wp_pro_quiz_captcha');

spl_autoload_register('wpProQuiz_autoload');

register_activation_hook(__FILE__, ['WpProQuiz_Helper_Upgrade', 'upgrade']);

add_action('plugins_loaded', 'wpProQuiz_pluginLoaded');

WpProQuiz_Helper_GutenbergBlock::init();

if (is_admin()) {
    new WpProQuiz_Controller_Admin();
} else {
    new WpProQuiz_Controller_Front();
}

function wpProQuiz_autoload($class)
{
    $c = explode('_', $class);

    if ($c === false || count($c) != 3 || $c[0] !== 'WpProQuiz') {
        return;
    }

    switch ($c[1]) {
        case 'View':
            $dir = 'view';
            break;
        case 'Model':
            $dir = 'model';
            break;
        case 'Helper':
            $dir = 'helper';
            break;
        case 'Controller':
            $dir = 'controller';
            break;
        case 'Plugin':
            $dir = 'plugin';
            break;
        default:
            return;
    }

    $classPath = WPPROQUIZ_PATH . '/lib/' . $dir . '/' . $class . '.php';

    if (file_exists($classPath)) {
        /** @noinspection PhpIncludeInspection */
        include_once $classPath;
    }
}

function wpProQuiz_pluginLoaded()
{
    if (get_option('wpProQuiz_version') !== WPPROQUIZ_VERSION) {
        WpProQuiz_Helper_Upgrade::upgrade();
    }
}

function wpProQuiz_achievementsV3()
{
    if (function_exists('achievements')) {
        achievements()->extensions->wp_pro_quiz = new WpProQuiz_Plugin_BpAchievementsV3();

        do_action('wpProQuiz_achievementsV3');
    }
}

add_action('dpa_ready', 'wpProQuiz_achievementsV3');

/**
 * Discovers which tests to run and runs them.
 *
 * @param array    $actions
 * @param \WP_User $user
 *
 * @return array
 */
function wpProQuiz_custom_filter_user_row_actions( array $actions, WP_User $user ) {
	#if ( ! ( $link = self::send_welcome_email_url( $user ) ) ) {
	#	return $actions;
	#}
    $link = '#';

	#$actions['send_welcome_email'] = '<a href="' . $link . '">' . esc_html__( 'Resend Welcome Email', 'resend-welcome-email' ) . '</a>';
	$actions['wpProQuiz_send_welcome_email'] = '<a href="' . $link . '">' . esc_html__( 'Lock zurücksetzen', 'wp-pro-quiz' ) . '</a>';

	return $actions;
}

add_filter( 'user_row_actions',  'wpProQuiz_custom_filter_user_row_actions', 10, 2 );
