;(function($) {
    'use strict';

    var Notify_Center = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function() {
            $( 'body' ).on( 'click', '.notify-list', this.change_read_status );
            $( 'body' ).on( 'click', '.read-area a', this.mark_all_as_read );
        },

        change_read_status: function(e) {
            var that = $(this);

            if ( ! that.hasClass('read') ) {
                that.removeClass('unread');
                that.addClass('read');

                $.post( wpNotifyCenter.ajaxurl, {
                        action: 'notification_read',
                        id: that.data('id')
                    }, function ( response ) {                        
                        // if ( response.success === true ) {
                        //     that.removeClass('unread');
                        //     that.addClass('read');
                        // }
                    }
                );
            }
        },

        mark_all_as_read: function(e) {
            var that = $(this);

            $('.notification-content').find('li').each(function(index, value) {
                if ( ! $(value).hasClass('read') ) {
                    $(value).removeClass('unread');
                    $(value).addClass('read');
                }
            });
                
            $.post( wpNotifyCenter.ajaxurl, {
                action: 'mark_all_read'
            }, function ( response ) {});
        },

    };

    $(function() {
        Notify_Center.initialize();
    });
})(jQuery);



function nc_toggle_notification() {
    var wrap = jQuery('#wp-notification-wrap');
    if ( wrap.is(':visible') ) {
        wrap.hide();
    } else {
        wrap.show();
    }
}

nc_toggle_notification();