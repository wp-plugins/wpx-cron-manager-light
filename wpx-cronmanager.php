<?php

/**
 * WPXCronManager is the main class of this plugin.
 * This class extends WPDKWordPressPlugin in order to make easy several WordPress functions.
 *
 * @class              WPXCronManager
 * @author             wpXtreme, Inc.
 * @copyright          Copyright (C) wpXtreme, Inc..
 * @date               2014-02-04
 * @version            1.0.0
 *
 */
final class WPXCronManager extends WPXPlugin {

  /**
   * Create and return a singleton instance of WPXCronManager class
   *
   * @param string $file The main file of this plugin. Usually __FILE__ (main.php). If missing, this function is used only
   *                     to get current instance, if it exists.
   *
   * @return WPXCronManager
   */
  public static function boot( $file = null )
  {
    static $instance = null;
    if ( is_null( $instance ) && ( !empty( $file ) ) ) {
      $instance = new self( $file );
    }
    return $instance;
  }

  /**
   * Return the singleton instance of WPXCronManager class
   *
   * @return WPXCronManager|NULL
   */
  public static function getInstance()
  {
    return self::boot();
  }

  /**
   * Create an instance of WPXCronManager class
   *
   * @param string $file The main file of this plugin. Usually __FILE__ (main.php)
   *
   * @return WPXCronManager object instance
   */
  public function __construct( $file = null )
  {
    parent::__construct( $file );

    // Manage cron jobs
    add_action( 'init', array( 'WPXCronManagerCronModel', 'init' ), 100 );

    // Remove an event
    add_filter( 'schedule_event', array( $this, 'schedule_event' ) );
  }

  /**
   * Disable a cron hook
   *
   *     $event = (object) array(
   *       'hook' => $hook,
   *       'timestamp' => $timestamp,
   *       'schedule' => $recurrence,
   *       'args' => $args,
   *       'interval' => $schedules[$recurrence]['interval']
   *     );
   *
   * @param object $event An event
   */
  public function schedule_event( $event )
  {
    // Disable list
    $disabled = get_site_option( WPXCronManagerCronModel::OPTION_CRON_JOBS, array() );

    // Nothing
    if ( empty( $disabled ) ) {
      return $event;
    }

    // Remove from database options
    if ( wp_next_scheduled( $event->hook, $event->args ) ) {
      wp_unschedule_event( $event->timestamp, $event->hook, $event->args );
    }

    // If this hook is disable
    if ( in_array( $event->hook, $disabled ) ) {

      // Disable
      return false;
    }

    return $event;
  }

  /**
   * Register all autoload classes
   */
  public function classesAutoload()
  {
    $includes = array(
      $this->classesPath . 'admin/wpxcronmanager-admin.php'     => 'WPXCronManagerAdmin',
      $this->classesPath . 'core/wpxcm-ajax.php'                => 'WPXCronManagerAjax',
      $this->classesPath . 'cron/wpxcm-cron-viewcontroller.php' => 'WPXCronManagerCronListTableViewController',
      $this->classesPath . 'cron/wpxcm-cron.php'                => 'WPXCronManagerCronModel'
    );

    return $includes;
  }


  /**
   * Catch for admin
   */
  public function admin()
  {
    WPXCronManagerAdmin::init();
  }

  /**
   * Catch for ajax
   */
  public function ajax()
  {
    WPXCronManagerAjax::init();
  }

}