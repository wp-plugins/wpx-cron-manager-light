<?php

/**
 * Description
 *
 * @class           WPXCronManagerCronListTableViewController
 * @author          =undo= <info@wpxtre.me>
 * @copyright       Copyright (C) 2012-2014 wpXtreme Inc. All Rights Reserved.
 * @date            2014-52-04
 * @version         1.0.0
 *
 */
class WPXCronManagerCronListTableViewController extends WPDKListTableViewController {

  // Option name for column e item per page
  const OPTION = 'wpxcm_cron_per_page';

  /**
   * Instance of Model class
   *
   * @var WPXCronManagerCronModel $model
   */
  public $model;

  /**
   * Return a singleton instance of WPXCronManagerCronListTableViewController class
   *
   * @return WPXCronManagerCronListTableViewController
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
   * Create an instance of WPXCronManagerCronListTableViewController class
   *
   * @return WPXCronManagerCronListTableViewController
   */
  public function __construct()
  {

    // Get model
    $this->model = WPXCronManagerCronModel::init();

    $args = array(
      'singular' => WPXCronManagerCronModel::COLUMN_HOOK_NAME,
      'plural'   => WPXCronManagerCronModel::LIST_TABLE_PLURAL,
      'ajax'     => false,
      // Added to fix
      'screen'   => 'toplevel_page_wpx-cron-manager'
    );

    parent::__construct( 'wpxcm-cron', __( 'Registered Cron', WPXCRONMANAGER_TEXTDOMAIN ), $args );

    // Remove Add New button
    add_filter( 'wpdk_listtable_viewcontroller_add_new', '__return_false' );
  }

  /**
   * This delegate method is called before load the view
   */
  public function load()
  {

    if ( in_array( $this->action(), array( 'edit', 'new' ) ) ) {
      add_filter( 'screen_options_show_screen', '__return_false' );

      return;
    }

    // Screen options
    global $wpxcm_cron_viewcontroller;

    $args = array(
      'label'   => __( 'Items per page', WPXCRONMANAGER_TEXTDOMAIN ),
      'default' => 10,
      'option'  => self::OPTION
    );
    add_screen_option( 'per_page', $args );

    $wpxcm_cron_viewcontroller = $this;
  }

  /**
   * Fires when styles are printed for a specific admin page based on $hook_suffix.
   *
   * @since WP 2.6.0
   * @since 1.6.0
   */
  public function admin_print_styles()
  {
    wp_enqueue_style( 'wpxcm-cron', WPXCRONMANAGER_URL_CSS . 'wpxcm-cron.css', array(), WPXCRONMANAGER_VERSION );
  }

  /**
   * Loading scripts and styles
   */
  public function admin_head()
  {
    wp_enqueue_script( 'wpxcm-cron', WPXCRONMANAGER_URL_JAVASCRIPT . 'wpxcm-cron.js', array( 'jquery' ), WPXCRONMANAGER_VERSION, true );
  }

  /**
   * Added extra tools filter in table nav
   *
   * @param string $which Top or bottom table nav
   */
  public function extra_tablenav( $which )
  {
    if ( $which == 'top' ) {
      //      $table_nav_view = new WPXSmartShopProducersTableNavView();
      //      $table_nav_view->display();
    }
  }

  /**
   * Display a content of cel for a column.
   *
   * @param array  $item        The item
   * @param string $column_name Column name
   *
   * @return mixed|string
   */
  public function column_default( $item, $column_name )
  {
    switch ( $column_name ) {

      // Column Hook name (id)
      case WPXCronManagerCronModel::COLUMN_HOOK_NAME:
        return $this->actions_column( $item, $item[ $column_name ], $item[ WPXCronManagerCronModel::COLUMN_STATUS ] );
        break;

      // Column next run
      case WPXCronManagerCronModel::COLUMN_NEXT_RUN:

        if ( wpdk_is_bool( $item[ WPXCronManagerCronModel::COLUMN_STATUS ] ) ) {

          // @todo preferences
          $date_format = 'M j, Y @ G:i';

          return date_i18n( $date_format, $item[ $column_name ] );
        }
        // Disable
        else {
          return '-';
        }
        break;

      // Count Down
      case WPXCronManagerCronModel::COLUMN_COUNT_DOWN:

        if ( wpdk_is_bool( $item[ WPXCronManagerCronModel::COLUMN_STATUS ] ) ) {

          $time = $item[ WPXCronManagerCronModel::COLUMN_NEXT_RUN ];
          if ( ! empty( $time ) ) {
            $in  = ( $time > time() ) ? __( 'in' ) . ' ' : '';
            $ago = ( $time < time() ) ? ' ' . __( 'ago' ) : '';

            // Javascript real-time count down
            if ( empty( $in ) ) {
              return WPDKDateTime::elapsedString( $item[ WPXCronManagerCronModel::COLUMN_NEXT_RUN ] );
              //return human_time_diff( $time ) . $ago;
            }
            else {
              $wait = WPDKGlyphIcons::html( WPDKGlyphIcons::SPIN1 ) . __( 'wait...' );
              return sprintf( '%s <span data-cron="%s" class="wpdk-ui-countdown" data-time="%s">%s</span>', $in, $item[ WPXCronManagerCronModel::COLUMN_HOOK_NAME ], ( $time - time() ) * 1000, $wait );
            }
          }

          //return WPDKDateTime::elapsedString( $item[ WPXCronManagerCronModel::COLUMN_NEXT_RUN ] );
                }
        // Disable
        else {
          return '-';
        }
        break;

      // Start Now
      case WPXCronManagerCronModel::COLUMN_EXECUTE:
        $hook_name = $item[ WPXCronManagerCronModel::COLUMN_HOOK_NAME ];
        $signature = isset( $item[ WPXCronManagerCronModel::COLUMN_SIGNATURE ] ) ? $item[ WPXCronManagerCronModel::COLUMN_SIGNATURE ] : '';
        WPDKHTML::startCompress(); ?>
        <button data-cron="<?php echo $hook_name ?>"
                data-signature="<?php echo $signature ?>"
                class="button button-primary wpxcm-button-start-now">
          <?php _e( 'Now!', WPXCRONMANAGER_TEXTDOMAIN ) ?>
        </button>
        <?php
        return WPDKHTML::endCompress();

        break;

      // Column status
      case WPXCronManagerCronModel::COLUMN_STATUS:
        $item    = array(
          'name'       => 'wpxcm-enable',
          'id'         => 'swipe-enable-' . $item[ WPXCronManagerCronModel::COLUMN_HOOK_NAME ],
          'userdata'   => $item[ WPXCronManagerCronModel::COLUMN_HOOK_NAME ],
          'afterlabel' => '',
          'title'      => '', // @too display a readable status
          'value'      => $item[ WPXCronManagerCronModel::COLUMN_STATUS ]
        );
        $control = new WPDKUIControlSwipe( $item );

        return $control->html();
        break;

      default:
        return $item[ $column_name ];
        break;

    }
  }
}