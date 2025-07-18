<?php 
/**
 * Plugin Name: WP Inactivity Notifier
 * Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-inactivity-notifier/
 * Description: WP Inactivity Notifier est un plugin conçu pour surveiller l'inactivité de contenu sur votre site WordPress. Il envoie des notifications par email aux administrateurs lorsqu'aucune publication récente n’est détectée, assurant ainsi que votre site reste actif et engageant. Il propose des rappels récurrents pour encourager la mise à jour de votre contenu.
 * Version: 1.0
 * Author: Kevin Benabdelhak
 * Author URI: https://kevin-benabdelhak.fr/
 * Contributors: kevinbenabdelhak, jbgouttes
 */

if (!defined('ABSPATH')) {
    exit;
}



if ( !class_exists( 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
}
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$monUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/kevinbenabdelhak/WP-Inactivity-Notifier/', 
    __FILE__,
    'wp-inactivity-notifier' 
);
$monUpdateChecker->setBranch('main');


require_once plugin_dir_path(__FILE__) . 'check.php';
require_once plugin_dir_path(__FILE__) . 'cron/cron.php';
require_once plugin_dir_path(__FILE__) . 'widget/widget.php';
require_once plugin_dir_path(__FILE__) . 'widget/add-widget.php';
require_once plugin_dir_path(__FILE__) . 'options/options.php';
require_once plugin_dir_path(__FILE__) . 'options/add-options.php';