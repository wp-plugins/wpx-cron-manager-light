<?php

/**
 * Cron Model used in list table view
 *
 * @class           WPXCronManagerCronModel
 * @author          =undo= <info@wpxtre.me>
 * @copyright       Copyright (C) 2012-2014 wpXtreme Inc. All Rights Reserved.
 * @date            2014-02-04
 * @version         1.0.0
 *
 */
class WPXCronManagerCronModel extends WPDKListTableModel {

  // Columns
  const COLUMN_HOOK_NAME = 'hook_name';
  const COLUMN_SCHEDULE = 'schedule';
  const COLUMN_SIGNATURE = 'signature';
  const COLUMN_NEXT_RUN = 'next_run';
  const COLUMN_COUNT_DOWN = 'count_down';
  const COLUMN_EXECUTE = 'execute';
  const COLUMN_STATUS = 'status';

  // Actions
  const ACTION_DISABLE = 'action_disable';
  const ACTION_ENABLE = 'action_enable';

  // Status
  const STATUS_ENABLED = 'enabled';
  const STATUS_DISABLED = 'disabled';

  // List table
  const LIST_TABLE_PLURAL = 'crons';

  // Own cron jobs list
  const OPTION_CRON_JOBS = 'wpxcm_disabled_cron';

  /**
   * List of cron jobs disabled
   *
   * @var array $disabled
   */
  public $disabled = array();

  /**
   * All registered cron
   *
   * @var array $cron
   */
  public $cron = array();

  /**
   * Return a singleton instance of WPXCronManagerCronModel class
   *
   * @return WPXCronManagerCronModel
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
   * Create an instance of WPXCronManagerCronModel class
   *
   * @return WPXCronManagerCronModel
   */
  public function __construct()
  {
    // Get disabled cron jobs
    $this->disabled = get_site_option( self::OPTION_CRON_JOBS, array() );

    /*
     * Get registered cron
     *
     * array(3) {
     *   [1394565420]=> array(1) {
     *     ["wp_maybe_auto_update"]=> array(1) {
     *       ["40cd750bba9870f18aada2478b24840a"]=> array(3) {
     *         ["schedule"]=> string(10) "twicedaily"
     *         ["args"]=> array(0) { }
     *         ["interval"]=> int(43200)
     *       }
     *     }
     *   }
     *   [1394569617]=> array(3) {
     *     ["wp_version_check"]=> array(1) {
     *       ["40cd750bba9870f18aada2478b24840a"]=> array(3) {
     *         ["schedule"]=> string(10) "twicedaily"
     *         ["args"]=> array(0) { }
     *         ["interval"]=> int(43200)
     *       }
     *     }
     *     ["wp_update_plugins"]=> array(1) {
     *       ["40cd750bba9870f18aada2478b24840a"]=> array(3) {
     *         ["schedule"]=> string(10) "twicedaily"
     *         ["args"]=> array(0) { }
     *         ["interval"]=> int(43200)
     *       }
     *     }
     *     ["wp_update_themes"]=> array(1) {
     *       ["40cd750bba9870f18aada2478b24840a"]=> array(3) {
     *         ["schedule"]=> string(10) "twicedaily"
     *         ["args"]=> array(0) { }
     *         ["interval"]=> int(43200)
     *       }
     *     }
     *   }
     *   [1394612818]=> array(1) {
     *     ["wp_scheduled_delete"]=> array(1) {
     *       ["40cd750bba9870f18aada2478b24840a"]=> array(3) {
     *         ["schedule"]=> string(5) "daily"
     *         ["args"]=> array(0) { }
     *         ["interval"]=> int(86400)
     *       }
     *     }
     *   }
     * }
     *
     */
    $this->cron = _get_cron_array();

    // Init the model and process action before wp is loaded
    parent::__construct();
  }

  /**
   * Return columns
   *
   * @return array
   */
  public function get_columns()
  {
    $columns = array(
      'cb'                    => '<input type="checkbox" />',
      self::COLUMN_NEXT_RUN   => __( 'Next Run', WPXCRONMANAGER_TEXTDOMAIN ),
      self::COLUMN_HOOK_NAME  => __( 'Hook name', WPXCRONMANAGER_TEXTDOMAIN ),
      self::COLUMN_SCHEDULE   => __( 'Schedule', WPXCRONMANAGER_TEXTDOMAIN ),
      self::COLUMN_COUNT_DOWN => __( 'Count Down', WPXCRONMANAGER_TEXTDOMAIN ),
      self::COLUMN_EXECUTE    => __( 'Execute', WPXCRONMANAGER_TEXTDOMAIN ),
      self::COLUMN_STATUS     => __( 'Status', WPXCRONMANAGER_TEXTDOMAIN ),
    );

    return $columns;
  }

  /**
   * Return the sortable columns
   *
   * @return array
   */
  public function get_sortable_columns()
  {
    $sortable_columns = array();
    return $sortable_columns;
  }

  /**
   * Return a key value pairs array with statuses supported.
   * You can override this method to return your own statuses.
   *
   * @return array
   */
  public function get_statuses()
  {
    // Get the registered schedules
    $registered_schedules = wp_get_schedules();

    $schedules = array(
      WPDKDBTableRowStatuses::ALL => __( 'All', WPXCRONMANAGER_TEXTDOMAIN ),
    );

    foreach ( $registered_schedules as $key => $value ) {
      $schedules[ $key ] = $value['display'];
    }

    // Added enabled/disabled
    $schedules[ self::STATUS_ENABLED ]  = __( 'Enabled', WPXCRONMANAGER_TEXTDOMAIN );
    $schedules[ self::STATUS_DISABLED ] = __( 'Disabled', WPXCRONMANAGER_TEXTDOMAIN );

    return $schedules;
  }

  /**
   * Return the count of records for a status
   *
   * @param string $status
   *
   * @return int
   */
  public function count()
  {
      // Prepare result
      $results = array();

      // Get status/schedule
      $schedules = $this->get_statuses();

      foreach ( array_keys( $schedules ) as $key ) {
        $results[ $key ] = 0;
      }

      // Get values
      foreach ( $this->cron as $timestamp => $cronhooks ) {
        $results[ WPDKDBTableRowStatuses::ALL ] += count( $cronhooks );

        // Count for schedules
        foreach ( (array)$cronhooks as $hook => $events ) {
          foreach ( (array)$events as $event ) {

            if ( isset( $schedules[ $event['schedule'] ] ) ) {
              $results[ $event['schedule'] ] += 1;
            }

          }
        }
      }

      // Added disabled
      if ( !empty( $this->disabled ) ) {

        // Calculate
        $results[ self::STATUS_DISABLED ] = count( $this->disabled );
        $results[ self::STATUS_ENABLED ] = absint( $results[ WPDKDBTableRowStatuses::ALL ] - $results[ self::STATUS_DISABLED ] );

        // Total
        $results[ WPDKDBTableRowStatuses::ALL ] += $results[ self::STATUS_DISABLED ];
      }

      // Return counts
      return $results;
  }


  /**
   * Return a key value pairs array with status key => count.
   *
   * @return array
   */
  public function get_count_statuses()
  {
    $counts = $this->count( self::COLUMN_STATUS );

    return $counts;
  }

  /**
   * Return the right inline action for the current status
   *
   * @param mixed  $item
   * @param string $status
   *
   * @return array
   */
  public function get_actions_with_status( $item, $status )
  {
    $actions = array(
      self::ACTION_DISABLE => __( 'Disable', WPXCRONMANAGER_TEXTDOMAIN ),
      self::ACTION_ENABLE  => __( 'Enable', WPXCRONMANAGER_TEXTDOMAIN ),
    );

    if ( wpdk_is_bool( $item[self::COLUMN_STATUS] ) ) {
      unset( $actions[self::ACTION_ENABLE] );
    }
    else {
      unset( $actions[self::ACTION_DISABLE] );
    }

    return $actions;
  }

  /**
   * Return the right combo menu bulk actions for the current status
   *
   * @param string $status
   *
   * @return array
   */
  public function get_bulk_actions_with_status( $status )
  {
    switch ( $status ) {
      case self::STATUS_ENABLED:
        $actions = array(
          self::ACTION_DISABLE => __( 'Disable', WPXCRONMANAGER_TEXTDOMAIN ),
        );
        break;

      case self::STATUS_DISABLED:
        $actions = array(
          self::ACTION_ENABLE  => __( 'Enable', WPXCRONMANAGER_TEXTDOMAIN ),
        );
        break;

      default:
        $actions = array(
          self::ACTION_DISABLE => __( 'Disable', WPXCRONMANAGER_TEXTDOMAIN ),
          self::ACTION_ENABLE  => __( 'Enable', WPXCRONMANAGER_TEXTDOMAIN ),
        );
        break;
    }
    return $actions;
  }

  /**
   * Process actions
   */
  public function process_bulk_action()
  {
    // Get the shortocode id if exists
    $id = isset( $_REQUEST[ self::COLUMN_HOOK_NAME ] ) ? $_REQUEST[ self::COLUMN_HOOK_NAME ] : '';

    // Process the action
    switch ( $this->current_action( self::LIST_TABLE_PLURAL ) ) {

      // Enable
      case self::ACTION_ENABLE:
        $hooks = (array)$id;
        $this->action_result( $this->enable( $hooks ) );
        break;

      // Disable
      case self::ACTION_DISABLE:
        $hooks = (array)$id;
        $this->action_result( $this->enable( $hooks, false ) );
        break;
    }

    parent::process_bulk_action();
  }

  // -------------------------------------------------------------------------------------------------------------------
  // CRUD
  // -------------------------------------------------------------------------------------------------------------------

  /**
   * Return the shortcodes items enable/disable
   *
   * @param array $args Optional. Arguments query.
   *
   * @return array
   */
  public function select( $args = array() )
  {
    $items     = array();
    $schedules = wp_get_schedules();

    // Defaults args
    $defaults = array(
      self::COLUMN_STATUS    => isset( $_REQUEST[ self::COLUMN_STATUS ] ) ? $_REQUEST[ self::COLUMN_STATUS ] : '',
      self::COLUMN_HOOK_NAME => array()
    );

    // Merging
    $args = wp_parse_args( $args, $defaults );

    // Where for status/schedule
    $status = $args[ self::COLUMN_STATUS ];

    foreach ( $this->cron as $timestamp => $cronhooks ) {
      foreach ( (array)$cronhooks as $hook => $events ) {
        foreach ( (array)$events as $sig => $event ) {

          // Filter $schedule
          if ( !empty( $status ) && !in_array( $status, array( WPDKDBTableRowStatuses::ALL, self::STATUS_DISABLED, self::STATUS_ENABLED ) ) ) {
            if ( $status != $event['schedule'] ) {
              continue;
            }
          }
          // Disable
          elseif ( !empty( $status ) && $status == self::STATUS_DISABLED && !in_array( $hook, $this->disabled ) ) {
            continue;
          }

          $item = array(
            self::COLUMN_HOOK_NAME => $hook,
            self::COLUMN_SIGNATURE => $sig,
            self::COLUMN_NEXT_RUN  => wp_next_scheduled( $hook ),
            self::COLUMN_STATUS    => in_array( $hook, $this->disabled ) ? 'off' : 'on'
          );

          if ( $event['schedule'] ) {
            $item[self::COLUMN_SCHEDULE] = $schedules[$event['schedule']]['display'];
          }
          // Single event
          else {
            $item[self::COLUMN_SCHEDULE] = __( 'One-time', WPXCRONMANAGER_TEXTDOMAIN );
          }
          $items[$hook] = $item;
        }
      }
    }

    // Check in disable list
    if ( !empty( $this->disabled ) && !empty( $status ) && in_array( $status, array( WPDKDBTableRowStatuses::ALL, self::STATUS_DISABLED ) ) ) {
      foreach ( $this->disabled as $hook ) {
        if ( !isset( $items[ $hook ] ) ) {
          $items[ $hook ] = array(
            self::COLUMN_HOOK_NAME => $hook,
            self::COLUMN_NEXT_RUN  => false,
            self::COLUMN_SCHEDULE  => false,
            self::COLUMN_STATUS    => 'off'
          );
        }
      }
    }

    // Where for hook
    if ( ! empty( $args[ self::COLUMN_HOOK_NAME ] ) ) {
      foreach ( $items as $key => $value ) {
        if ( ! in_array( $key, (array)$args[ self::COLUMN_HOOK_NAME ] ) ) {
          unset( $items[ $key ] );
        }
      }
    }

    return $items;
  }

  /**
   * Execute immediately a cron job event. Return TRUE if ok, FALSE if not found.
   *
   * @param string $hookname  Hook name
   * @param string $signature Signature for args
   *
   * @return book
   */
  public function execute( $hookname, $signature )
  {
    $no_found = false;

    // Loop in cron
    foreach ( $this->cron as $timestamp => $cronhooks ) {

      if ( isset( $cronhooks[ $hookname ][ $signature ] ) ) {
        $no_found = true;
        $args     = $cronhooks[ $hookname ][ $signature ]['args'];

        wp_clear_scheduled_hook( $hookname, $args );

        delete_transient( 'doing_cron' );
        wp_schedule_single_event( time() - 1, $hookname, $args );
        spawn_cron();
      }
    }

    return $no_found;
  }

  /**
   * Enable/disable
   *
   * @param array $hooks  A list of hooks
   * @param bool  $enable Optional. Default TRUE
   */
  public function enable( $hooks, $enable = true )
  {
    // Enable
    if ( $enable ) {
      $this->disabled = array_unique( array_diff( (array)$this->disabled, (array)$hooks ) );
    }
    // Disable
    else {
      $this->disabled = array_unique( array_merge( (array)$this->disabled, (array)$hooks ) );
    }

    // Update
    update_site_option( self::OPTION_CRON_JOBS, $this->disabled );

    return true;
  }

}