<?php
if ( wpdk_is_ajax() ) {

  /**
   * Ajax gateway engine
   *
   * @class              WPXCronManagerAjax
   * @author             =undo= <info@wpxtre.me>
   * @copyright          Copyright (C) 2012-2014 wpXtreme Inc. All Rights Reserved.
   * @date               2014-02-05
   * @version            1.0.0
   *
   */
  class WPXCronManagerAjax extends WPDKAjax {

    /**
     * Create or return a singleton instance of WPXCronManagerAjax
     *
     * @brief Create or return a singleton instance of WPXCronManagerAjax
     *
     * @return WPXCronManagerAjax
     */
    public static function getInstance()
    {
      $instance = null;
      if ( is_null( $instance ) ) {
        $instance = new self();
      }

      return $instance;
    }

    /**
     * Alias of getInstance();
     *
     * @brief Init the ajax register
     *
     * @return WPXCronManagerAjax
     */
    public static function init()
    {
      return self::getInstance();
    }

    /**
     * Return the array with the list of ajax allowed methods
     *
     * @breief Allow ajax action
     *
     * @return array
     */
    protected function actions()
    {
      $actionsMethods = array(
        'wpxcm_action_enable'  => false,
        'wpxcm_action_refresh' => false,
        'wpxcm_action_execute' => false,
      );

      return $actionsMethods;
    }

    /**
     * Refresh a single row.
     *
     * @return string
     */
    public function wpxcm_action_refresh()
    {
      $response = new WPDKAjaxResponse();

      // Get the cron hook name
      $cron = esc_attr( $_POST['cron'] );

      if ( empty( $cron ) ) {
        $response->error = __( 'No Cron Hook name!', WPXCRONMANAGER_TEXTDOMAIN );
        $response->json();
      }

      // Get model
      $model = WPXCronManagerCronModel::init();

      // Gets the row
      $list_table = WPXCronManagerCronListTableViewController::init();
      $items      = $model->select( array( WPXCronManagerCronModel::COLUMN_HOOK_NAME => $cron ) );
      $item       = array_shift( $items );

      // Prepare response
      $this->data = array();

      // HTML for single row
      WPDKHTML::startCompress();
      $list_table->single_row( $item );
      $response->data['row'] = WPDKHTML::endHTMLCompress();

      // Views
      WPDKHTML::startCompress();
      $list_table->views();
      $response->data['views'] = WPDKHTML::endHTMLCompress();

      $response->json();
    }

    /**
     * Enable/disable
     *
     * @return string
     */
    public function wpxcm_action_enable()
    {
      $response = new WPDKAjaxResponse();

      // Get the cron hook name
      $cron = esc_attr( $_POST['cron'] );

      if ( empty( $cron ) ) {
        $response->error = __( 'No Cron Hook name!', WPXCRONMANAGER_TEXTDOMAIN );
        $response->json();
      }

      // Get status
      $status = esc_attr( $_POST['enable'] );

      // Enable
      $enable = wpdk_is_bool( $status );

      // Get model
      $model = WPXCronManagerCronModel::init();

      // Update
      $model->enable( $cron, $enable );

      /**
       * Fires when a cron is disabled.
       */
      //do_action( 'wpxcm_refresh_disabled' );

      // Gets the row
      $list_table = WPXCronManagerCronListTableViewController::init();
      $items      = $model->select( array( WPXCronManagerCronModel::COLUMN_HOOK_NAME => $cron ) );
      $item       = array_shift( $items );

      // Prepare response
      $this->data = array();

      // HTML for single row
      WPDKHTML::startCompress();
      $list_table->single_row( $item );
      $response->data['row'] = WPDKHTML::endHTMLCompress();

      // Views
      WPDKHTML::startCompress();
      $list_table->views();
      $response->data['views'] = WPDKHTML::endHTMLCompress();

      $response->json();
    }

    /**
     * Execute a cron hook
     */
    public function wpxcm_action_execute()
    {
      $response = new WPDKAjaxResponse();

      // Get the cron hook name
      $cron      = esc_attr( $_POST['cron'] );
      $signature = esc_attr( $_POST['signature'] );

      if ( empty( $cron ) ) {
        $response->error = __( 'No Cron Hook name!', WPXCRONMANAGER_TEXTDOMAIN );
        $response->json();
      }

      $result = WPXCronManagerCronModel::init()->execute( $cron, $signature );

      $response->data = $result;
      $response->json();
    }
  }
}