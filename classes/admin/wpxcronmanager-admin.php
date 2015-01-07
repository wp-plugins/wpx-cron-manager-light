<?php
/**
 * Brief Description here
 *
 * ## Overview
 * Markdown here
 *
 * @class              WPXCronManagerAdmin
 * @author             wpXtreme, Inc.
 * @copyright          Copyright (C) 2013-2014 wpXtreme Inc. All Rights Reserved.
 * @date               2014-02-04
 * @version            1.0.0
 *
 */

class WPXCronManagerAdmin extends WPDKWordPressAdmin {

  // This is the minumun capability required to display admin menu item
  const MENU_CAPABILITY = 'manage_options';

  /**
   * Create and return a singleton instance of WPXCronManagerAdmin class
   *
   * @return WPXCronManagerAdmin
   */
  public static function init()
  {
    static $instance = null;
    if ( is_null( $instance ) ) {
      $instance = new self();
    }
    return $instance;
  }

  /**
   * Create an instance of WPXCronManagerAdmin class
   *
   * @return WPXCronManagerAdmin
   */
  public function __construct()
  {
    /**
     * @var WPXCronManager $plugin
     */
    $plugin = $GLOBALS['WPXCronManager'];
    parent::__construct( $plugin );

    // Manage the view controller options
    add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
  }

  /**
   * Filter a screen option value before it is set.
   *
   * The filter can also be used to modify non-standard [items]_per_page
   * settings. See the parent function for a full list of standard options.
   *
   * Returning false to the filter will skip saving the current option.
   *
   * @since WP 2.8.0
   *
   * @see   set_screen_options()
   *
   * @param bool|int $value  Screen option value. Default false to skip.
   * @param string   $option The option name.
   * @param int      $value  The number of rows to use.
   */
  public function set_screen_option( $status, $option, $value )
  {
    $options = array(
      WPXCronManagerCronListTableViewController::OPTION,
    );

    if ( in_array( $option, $options ) ) {
      return $value;
    }
    return $status;
  }

  /**
   * Called by WPDKWordPressAdmin parent when the admin head is loaded.
   *
   * @brief Admin head
   *
   * @param string $hook_suffix Hook suffix
   *
   */
  public function admin_enqueue_scripts( $hook_suffix )
  {
  }

  /**
   * Called when WordPress is ready to build the admin menu.
   * Sample hot to build a simple menu.
   *
   * @brief Admin menu
   */
  public function admin_menu()
  {
    // Hack for wpXtreme icon
    $icon_menu = $this->plugin->imagesURL . 'logo-16x16.png';

    $menus = array(
      'wpx-cron-manager' => array(
        'menuTitle'  => __( 'Cron Manager', WPXCRONMANAGER_TEXTDOMAIN ),
        'capability' => self::MENU_CAPABILITY,
        'icon'       => $icon_menu,
        'subMenus'   => array(

          array(
            'menuTitle'      => __( 'Cron list', WPXCRONMANAGER_TEXTDOMAIN ),
            'capability'     => self::MENU_CAPABILITY,
            'viewController' => 'WPXCronManagerCronListTableViewController',
          ),
        )
      )
    );

    WPXMenu::init( $menus, $this->plugin );


  }
}