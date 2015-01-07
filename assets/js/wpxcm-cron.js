/**
 * Admin Backend Area
 *
 * @class           WPXCronManagerCron
 * @author          wpXtreme, Inc.
 * @copyright       Copyright (C) 2013-2014 wpXtreme Inc. All Rights Reserved.
 * @date            2014-09-19
 * @version         1.0.1
 *
 * @history         1.0.1 - Introducing real time count down
 */

jQuery( function ( $ )
{
  "use strict";

  window.WPXCronManagerCron = (function ()
  {

    /**
     * This Object
     *
     * @type {{}}
     */
    var _WPXCronManagerCron = {
      version       : '1.0.1',
      init          : _init,
      REFRESH_SWIPE : 'refresh.wpxcm.swipe'
    };

    /**
     * Return an instance of WPXCronManagerCron object
     *
     * @return WPXCronManagerCron
     */
    function _init()
    {
      _initSwipeEnable();
      _initExecute();

      // Check if countdown exists - only in list table view
      if ( $( '.wpdk-ui-countdown' ).length ) {

        // First refresh
        _updateCoundDown();

        // The every 1 seconds
        setInterval( _updateCoundDown, 1000 );
      }

      return _WPXCronManagerCron;
    }

    /**
     * Update all span tag for reali time countdown.
     *
     * @private
     */
    function _updateCoundDown()
    {

      // Realtime Javascrip countdown
      $( '.wpdk-ui-countdown' ).each( function ()
      {
        _countDown( $( this ) );
      } );
    }

    /**
     * Colculate countdown.
     *
     * @param $item
     * @private
     */
    function _countDown( $item )
    {
      var diff = parseInt( $item.data( 'time' ) ),
        days = Math.floor( diff / (1000 * 60 * 60 * 24) ),
        hours = Math.floor( diff / (1000 * 60 * 60) ),
        mins = Math.floor( diff / (1000 * 60) ),
        secs = Math.floor( diff / 1000 );

      var dd = days,
        hh = hours - days * 24,
        mm = mins - hours * 60,
        ss = secs - mins * 60;

      function _pad( value )
      {
        return value > 9 ? value : '0' + value;
      };

      var output = '';

      output += empty( dd ) ? '' : dd + ' days ';
      output += _pad( hh ) + ':';
      output += _pad( mm ) + ':';
      output += _pad( ss );

      $item.html( output );

      // Stop timer
      if ( 0 == ( hh | mm | ss ) ) {
        $item.removeClass( 'wpdk-ui-countdown' );
        _refreshRow( $item );
      }

      // Update unixtime
      $item.data( 'time', (diff - 1000) );

    }

    /**
     * Refresh a single row.
     *
     * @private
     */
    function _refreshRow( $item )
    {
      // Ajax
      $.post( wpdk_i18n.ajaxURL, {
          action : 'wpxcm_action_refresh',
          cron   : $item.data( 'cron' )
        }, function ( data )
        {
          var response = new WPDKAjaxResponse( data );

          if ( empty( response.error ) ) {
            $item.parents( 'tr' ).replaceWith( response.data.row );
            $( 'ul.subsubsub' ).replaceWith( response.data.views );

            // Ask wpXtreme refresh
            $( document ).trigger( WPXtremeAdmin.REFRESH_TABLE_ACTIONS );
          }
          else {
            alert( response.error );
            return false;
          }
        }
      );
    }

    /**
     * Init swipe for enable/disable
     *
     * @private
     */
    function _initSwipeEnable()
    {
      $( document ).on( WPDKUIComponentEvents.SWIPE_CHANGED, '[id^="swipe-enable-"]', function ( el, knob, enabled )
      {
        // Ajax
        $.post( wpdk_i18n.ajaxURL, {
            action : 'wpxcm_action_enable',
            cron   : $( this ).data( 'userdata' ),
            enable : enabled
          }, function ( data )
          {
            var response = new WPDKAjaxResponse( data );

            if ( empty( response.error ) ) {
              $( knob ).parents( 'tr' ).replaceWith( response.data.row );
              $( 'ul.subsubsub' ).replaceWith( response.data.views );

              // Ask wpXtreme refresh
              $( document ).trigger( WPXtremeAdmin.REFRESH_TABLE_ACTIONS );
            }
            else {
              alert( response.error );
              return false;
            }
          }
        );
      } );
    }

    /**
     * Init button execute
     *
     * @private
     */
    function _initExecute()
    {
      $( 'button.wpxcm-button-start-now' ).on( 'click', function ()
      {
        var $button = $( this )

        $button
          .before( WPDKGlyphIcons.html( WPDKGlyphIcons.SPIN5 ) )
          .addClass( 'hide' )

        // Ajax
        $.post( wpdk_i18n.ajaxURL, {
            action    : 'wpxcm_action_execute',
            cron      : $( this ).data( 'cron' ),
            signature : $( this ).data( 'signature' )
          }, function ( data )
          {
            var response = new WPDKAjaxResponse( data );

            if ( empty( response.error ) ) {
              //
            }
            else {
              alert( response.error );
            }

            $button
              .removeClass( 'hide' )
              .prev( 'i' )
              .remove();

          }
        );
        return false;
      } );
    }

    return _init();

  })();

} );
