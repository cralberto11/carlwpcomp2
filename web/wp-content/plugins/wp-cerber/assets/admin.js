jQuery(document).ready(function ($) {

    /* WP Comments page */
    var comtable = 'table.wp-list-table.comments';

    if (typeof crb_lab_available !== 'undefined' && crb_lab_available && $(comtable).length) {
        $(comtable + " td.column-author").each(function (index) {
            var ip = $(this).find('a').last().text();
            var ip_id = cerber_get_id_ip(ip);
            //$(this).append('<p><img data-ip-id="' + ip_id + '" class="crb-no-hostname" src="' + crb_ajax_loader + '" style="float: none;"/></p>');
            $(this).append('<p><img data-ip-id="' + ip_id + '" class="crb-no-country" src="' + crb_ajax_loader + '" style="float: none;"/></p>');
        });
        //cerberLoadData('hostname');
        //cerberLoadData('country');
    }

    /* Load data with AJAX */

    if ($(".crb-no-country").length) {
        cerberLoadData('country');
    }

    if ($(".crb-no-hostname").length) {
        cerberLoadData('hostname');
    }

    function cerberLoadData(slug) {
        var ip_list = $(".crb-no-" + slug).map(
            function () {
                return $(this).data('ip-id');
            }
        );
        if (ip_list.length !== 0) {
            $.post(ajaxurl, {
                action: 'cerber_ajax',
                crb_ajax_slug: slug,
                crb_ajax_list: ip_list.toArray(),
                ajax_nonce: crb_ajax_nonce
            }, cerberSetData);
        }
    }

    function cerberSetData(server_response) {
        var server_data = $.parseJSON(server_response);
        if (!server_data['data']) {
            console.log('No data loaded from server!');
            return;
        }
        var data = server_data['data'];
        var slug = server_data['slug'];
        $(".crb-no-" + slug).each(function (index) {
            $(this).replaceWith(data[$(this).data('ip-id')]);
        });
    }

    // ACL management

    $(".acl-table .delete_entry").click(function () {
        /* if (!confirm('<?php _e('Are you sure?','wp-cerber') ?>')) return; */
        $.post(ajaxurl, {
                action: 'cerber_ajax',
                acl_delete: $(this).data('ip'),
                ajax_nonce: crb_ajax_nonce
            },
            onDeleteSuccess
        );
        /*$(this).parent().parent().fadeOut(500);*/
        /* $(this).closest("tr").FadeOut(500); */
    });
    function onDeleteSuccess(server_data) {
        var cerber_response = $.parseJSON(server_data);
        $('.delete_entry[data-ip="' + cerber_response['deleted_ip'] + '"]').parent().parent().fadeOut(300);
    }

    //

    /*
     $('#add-acl-black').submit(function( event ) {
     $(this).find('[name="add_acl_B"]').val($(this).find("button:focus").val());
     });
     */

    $(".cerber-dismiss").click(function () {
        $(this).closest('.cerber-msg').fadeOut(500);

        $.get(ajaxurl, {
                action: 'cerber_ajax',
                dismiss_info: 1,
                button_id: $(this).attr('id'),
            }
        );
    });

    $(".diag-text").on("keypress", function(e) {
        e.preventDefault();
    });

    function cerber_get_id_ip(ip) {
        var id = ip.replace(/\./g, '-');
        id = id.replace(/:/g, '_');

        return id;
    }

    /* Traffic */

    $('#crb-traffic tr.crb-toggle td.crb-request').click(function () {
        var request_details = $(this).parent().next();
        //request_details.slideToggle(100);
        request_details.toggle();
        //request_details.data('session-id');
    });

    $('#crb-traffic tr').mouseenter(function() {
        $(this).find('a.crb-traffic-more').show();
    });

    $('#crb-traffic tr').mouseleave(function() {
        $(this).find('a.crb-traffic-more').hide();
    });

});
