var ph_lightbox_open = false; // Used to determine if a details lightbox is open and therefore which post ID (stored in ph_lightbox_post_id) to pass through to AJAX requests
var ph_lightbox_post_id;

jQuery( function($){
    
    $('.propertyhive_meta_box #property_rooms').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'h3'
     });
     
     $('.propertyhive_meta_box #property_features').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_photo_urls').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_floorplan_urls').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_brochure_urls').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_epc_urls').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_virtual_tours').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

    initialise_datepicker();

    // TABS
    $('ul.ph-tabs').show();
    $('div.panel-wrap').each(function(){
        $(this).find('div.panel:not(:first)').hide();
    });
    $('ul.ph-tabs a').click(function(){
        var panel_wrap =  $(this).closest('div.panel-wrap');
        $('ul.ph-tabs li', panel_wrap).removeClass('active');
        $(this).parent().addClass('active');
        $('div.panel', panel_wrap).hide();
        $( $(this).attr('href') ).show();
        return false;
    });
    $('ul.ph-tabs li:visible').eq(0).find('a').click();
    
    // Notes
    $(document).on( 'click', '[id^=\'propertyhive_\'][id$=\'_notes_container\'] a.add_note', function() 
    {
        var section = $(this).attr('data-section');

        if ( ! $('#propertyhive_' +  section + '_notes_container textarea#add_note').val() ) return;

        if ( $(this).text() == 'Adding...' ) { return false; }

        $(this).html('Adding...');
        $(this).attr('disabled', 'disabled');
 
        var data = {
            action:         'propertyhive_add_note',
            post_id:        ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            note:           $('#propertyhive_' +  section + '_notes_container textarea#add_note').val(),
            note_type:      'propertyhive_note',
            security:       propertyhive_admin_meta_boxes.add_note_nonce,
        };

        if ( $('#propertyhive_' +  section + '_notes_container input[name=\'pinned\']').prop('checked') )
        {
            data.pinned = '1';
        }

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            var data = {
                action:         'propertyhive_get_notes_grid',
                post_id:        ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                section:        section,
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_' +  section + '_notes_container').html(response);
            }, 'html');
        });

        return false;
    });

    $(document).on( 'click', '[id^=\'propertyhive_\'][id$=\'_notes_container\'] a.delete_note', function() 
    {
        var section = $(this).attr('data-section');

        if ( $(this).text() == 'Deleting...' ) { return; }

        var confirm_box = confirm('Are you sure you wish to delete this note?');
        if (!confirm_box)
        {
            return false;
        }

        $(this).html('Deleting...');

        var note = $(this).closest('li.note');
        
        var data = {
            action:         'propertyhive_delete_note',
            note_id:        $(note).attr('rel'),
            security:       propertyhive_admin_meta_boxes.delete_note_nonce,
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            var data = {
                action:         'propertyhive_get_notes_grid',
                post_id:        ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                section:        section,
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_' +  section + '_notes_container').html(response);
            }, 'html');
        }, 'json');

        return false;
    });

    $(document).on( 'click', '[id^=\'propertyhive_\'][id$=\'_notes_container\'] a.toggle_note_pinned', function()
    {
        var section = $(this).attr('data-section');

        if ( $(this).text().indexOf('...') >= 0 ) { return; }

        var note = $(this).closest('li.note');

        if ( note.find('div.pinned').length > 0 )
        {
            var loading_text = 'Unpinning...';
        }
        else
        {
            var loading_text = 'Pinning...';
        }
        $(this).html(loading_text);

        var data = {
            action:           'propertyhive_toggle_note_pinned',
            note_id:          $(note).attr('rel'),
            security:         propertyhive_admin_meta_boxes.pin_note_nonce,
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {

            var data = {
                action:         'propertyhive_get_notes_grid',
                post_id:        ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                section:        section,
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_' +  section + '_notes_container').html(response);
            }, 'html');

        }, 'json');

        return false;

    });

    // Notes filter
    $('.notes-filter a').click(function(e)
    {
        e.preventDefault();

        var note_type = $(this).attr('data-filter-class');
        var section = $(this).attr('data-section');

        if ( note_type == '*' )
        {
            // show all notes
            $('#propertyhive_' +  section + '_notes_container .record_notes li').show();

            if ( $('#propertyhive_' +  section + '_notes_container .record_notes li').length > 1 )
            {
                $('#propertyhive_' +  section + '_notes_container .record_notes li#no_notes').hide();
            }
        }
        else
        {
            $('#propertyhive_' +  section + '_notes_container .record_notes li').hide();
            $('#propertyhive_' +  section + '_notes_container .record_notes li.' + note_type).show();
        }

        $(this).parent().parent().find('a').removeClass('current');
        $(this).addClass('current');
    });

    // Key Dates
    $('[id=\'propertyhive-management-dates\']').on( 'click', 'a.add_key_date', function() {

        if ( !$('#_add_key_date_description').val() || !$('#_add_key_date_due').val() ) return;

        if ( $(this).text() == 'Adding...' ) { return false; }

        $(this).html('Adding...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:               'propertyhive_add_key_date',
            post_id:              propertyhive_admin_meta_boxes.post_id,
            key_date_type:        $('#_add_key_date_type').val(),
            key_date_description: $('#_add_key_date_description').val(),
            key_date_due:         $('#_add_key_date_due').val(),
            key_date_hours:       $('#_add_key_date_due_hours').val(),
            key_date_minutes:     $('#_add_key_date_due_minutes').val(),
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            var data = {
                action:  'propertyhive_get_management_dates_grid',
                post_id: propertyhive_admin_meta_boxes.post_id,
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_management_dates_container').html(response);
                initialise_datepicker();
            }, 'html');
        });

        return false;
    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '#filter-key-dates-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_management_dates_grid',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_type_id: $('#_type_id_filter').val(),
            selected_status:  $('#_date_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_management_dates_container').html(response);
            initialise_datepicker();
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '.meta-box-quick-edit', function() {

        var post_id = $(this).attr('id');
        var original_row = $('.post-' + post_id);

        $('.quick-edit-row').hide();
        $('.key-date-row').show();
        original_row.hide();

        if ( $('#quick-edit-' + post_id).length > 0 )
        {
            $('#quick-edit-' + post_id).show();
        }
        else
        {
            original_row.after('<tr id="quick-edit-' + post_id + '" class="quick-edit-row"><td colspan="4">Loading...</td></tr>');

            var data = {
                action: 'propertyhive_get_key_dates_quick_edit_row',
                post_id: propertyhive_admin_meta_boxes.post_id,
                date_post_id: post_id,
                description: $('.post-' + post_id + ' .description .cell-main-content').text(),
                status: $('.post-' + post_id + ' .status .cell-main-content').text(),
                due_date_time: $('.post-' + post_id + ' .date_due .cell-main-content').text(),
                type: $('.post-' + post_id + ' .hidden-date-type-id').text(),
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                $('#quick-edit-' + post_id).html(response);
                initialise_datepicker();
            }, 'html');
        }

        return false;

    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '.save-quick-edit', function() {

        var date_post_id = $(this).attr('id');

        if ( $(this).text() == 'Saving...' ) { return false; }

        $(this).text('Saving...');
        $(this).attr('disabled', 'disabled');

        var quick_edit_row = $('#quick-edit-' + date_post_id);

        var data = {
            action: 'propertyhive_save_key_date',
            post_id: date_post_id,
            description: quick_edit_row.find('#date_description').val(),
            status: quick_edit_row.find('#key_date_status').val(),
            due_date_time: quick_edit_row.find('#date_due_quick_edit').val() + ' ' + quick_edit_row.find('#date_due_hours_quick_edit').val() + ':' + quick_edit_row.find('#date_due_minutes_quick_edit').val(),
            type: quick_edit_row.find('#date_type').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            var data = {
                action:           'propertyhive_get_management_dates_grid',
                post_id:          propertyhive_admin_meta_boxes.post_id,
                selected_type_id: $('#_type_id_filter').val(),
                selected_status:  $('#_date_status_filter').val(),
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_management_dates_container').html(response);
                initialise_datepicker();
            }, 'html');
        }, 'html');

    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '.cancel-quick-edit', function() {
        $('.quick-edit-row').hide();
        $('.post-' + $(this).attr('id')).show();
    });

    $('[id=\'propertyhive-property-tenancies\']').on( 'click', '#filter-property-tenancies-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_property_tenancies_grid',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_tenancy_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_property_tenancies_grid').html(response);
        }, 'html');

        return false;
    });
    
    // Multiselect
    $(".propertyhive_meta_box select.multiselect").chosen();

    $( document ).on('click', '.viewing-lightbox', function(e)
    {
        e.preventDefault();
        
        ph_open_details_lightbox($(this).attr('data-viewing-id'), 'viewing');
    });

    $( document ).on('click', '.propertyhive-lightbox-buttons a.button-close', function(e)
    {
        e.preventDefault();

        $.fancybox.close();
    });

    $( document ).on('click', '.propertyhive-lightbox-buttons a.button-prev', function(e)
    {
        e.preventDefault();

        var previous_post_id = false;
        $('a[data-viewing-id]').each(function()
        {
            var post_id = $(this).attr('data-viewing-id');

            if ( post_id == ph_lightbox_post_id )
            {
                ph_open_details_lightbox(previous_post_id, 'viewing');

                return;
            }

            previous_post_id = post_id;
        });
    });

    $( document ).on('click', '.propertyhive-lightbox-buttons a.button-next', function(e)
    {
        e.preventDefault();

        var use_next = false;
        $('a[data-viewing-id]').each(function()
        {
            var post_id = $(this).attr('data-viewing-id');

            if (use_next)
            {
                ph_open_details_lightbox(post_id, 'viewing');

                return false;
            }

            if ( post_id == ph_lightbox_post_id )
            {
                use_next = true;
            }
        });
    });
});

function ph_open_details_lightbox(post_id, section)
{
    ph_lightbox_post_id = post_id;

    jQuery.fancybox.close();

    jQuery.fancybox.open({
        src  : ajaxurl + '?action=propertyhive_get_' + section + '_lightbox&post_id=' + ph_lightbox_post_id,
        type : 'ajax',
        beforeLoad: function()
        {
            ph_lightbox_open = true;
        },
        afterShow: function()
        {
            // hide/show next/prev buttons
            var found_current = false;
            var previous_exist = false;
            var next_exist = false;
            jQuery('a[data-' + section + '-id]').each(function()
            {
                var post_id = jQuery(this).attr('data-' + section + '-id');

                if ( found_current )
                {
                    next_exist = true;
                }

                if ( post_id == ph_lightbox_post_id )
                {
                    // this is the lightbox being viewed
                    found_current = true;
                }
                else
                {
                    if ( !found_current )
                    {
                        previous_exist = true;
                    }
                }
            });

            if ( previous_exist )
            {
                jQuery('.propertyhive-lightbox-buttons a.button-prev').show();
            }
            if ( next_exist )
            {
                jQuery('.propertyhive-lightbox-buttons a.button-next').show();
            }
        },
        beforeClose: function()
        {
            ph_lightbox_open = false;
        }
    });
}

// VIEWINGS //
jQuery(window).on('load', function()
{
    //redraw_viewing_details_meta_box(); // called from within redraw_viewing_actions()
    redraw_viewing_actions();
});

function redraw_viewing_details_meta_box()
{
    if ( jQuery('#propertyhive_viewing_details_meta_box_container').length > 0 )
    {
        jQuery('#propertyhive_viewing_details_meta_box_container').html('Loading...');

        var data = {
            action:         'propertyhive_get_viewing_details_meta_box',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            security:       propertyhive_admin_meta_boxes.viewing_details_meta_nonce,
            readonly:       ph_lightbox_open
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            jQuery('#propertyhive_viewing_details_meta_box_container').html(response);
        }, 'html');
    }
}

function redraw_viewing_actions()
{
    if ( jQuery('#propertyhive_viewing_actions_meta_box_container').length > 0 )
    {
        jQuery('#propertyhive_viewing_actions_meta_box_container').html('Loading...');
        
        var data = {
            action:         'propertyhive_get_viewing_actions',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            jQuery('#propertyhive_viewing_actions_meta_box_container').html(response);
        }, 'html');
    }

    redraw_viewing_details_meta_box();
}

jQuery(document).ready(function($)
{
    $(document).on('click', 'a.viewing-action', function(e)
    {
        e.preventDefault();

        var this_href = $(this).attr('href');

        $(this).attr('disabled', 'disabled');

        if ( this_href == '#action_panel_viewing_carried_out' )
        {
            var data = {
                action:         'propertyhive_viewing_carried_out',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_email_applicant_booking_confirmation' )
        {
            var data = {
                action:         'propertyhive_viewing_email_applicant_booking_confirmation',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_email_owner_booking_confirmation' )
        {
            var data = {
                action:         'propertyhive_viewing_email_owner_booking_confirmation',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_feedback_not_required' )
        {
            var data = {
                action:         'propertyhive_viewing_feedback_not_required',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_revert_feedback_passed_on' )
        {
            var data = {
                action:         'propertyhive_viewing_feedback_passed_on',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_revert_pending' )
        {
            var data = {
                action:         'propertyhive_viewing_revert_pending',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_revert_feedback_pending' )
        {
            var data = {
                action:         'propertyhive_viewing_revert_feedback_pending',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        $('#propertyhive_viewing_actions_meta_box').stop().fadeOut(300, function()
        {
            $(this_href).stop().fadeIn(300, function()
            {
                
            });
        });
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.action-cancel', function(e)
    {
        e.preventDefault();

        redraw_viewing_actions();
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.cancelled-reason-action-submit', function(e)
    {
        e.preventDefault();

        $(this).attr('disabled', 'disabled');

        // Submit interested feedback
        var data = {
            action:         'propertyhive_viewing_cancelled',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            cancelled_reason: $('#_cancelled_reason').val(),
            security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            redraw_viewing_actions();
        }, 'json');
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.interested-feedback-action-submit', function(e)
    {
        e.preventDefault();

        $(this).attr('disabled', 'disabled');

        // Submit interested feedback
        var data = {
            action:         'propertyhive_viewing_interested_feedback',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            feedback:       $('#_interested_feedback').val(),
            security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            redraw_viewing_actions();
        }, 'json');
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.not-interested-feedback-action-submit', function(e)
    {
        e.preventDefault();

        $(this).attr('disabled', 'disabled');

        // Submit interested feedback
        var data = {
            action:         'propertyhive_viewing_not_interested_feedback',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            feedback:       $('#_not_interested_feedback').val(),
            security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            redraw_viewing_actions();
        }, 'json');
    })
});

function initialise_datepicker() {
    jQuery( ".date-picker" ).datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: true
    }).on("change", function(e) {
        var curDate = jQuery(this).val();
        var valid  = true;
        
        if ( curDate != '' )
        {
            var splitDate = curDate.split("-")
            if ( splitDate.length != 3 )
            {
                valid = false;
            }
            else
            {
                if ( splitDate[0].length != 4 || splitDate[1].length != 2 || splitDate[2].length != 2 )
                {
                    valid = false;
                }
            }

            if (!valid) 
            {
                alert("Invalid date entered. Please select a date from the calendar and ensure date is in the format YYYY-MM-DD");
            }
        }
    });
}

function add_months(date, months) {
    var d = date.getDate();
    date.setMonth(date.getMonth() + +months);
    if (date.getDate() != d)
    {
        date.setDate(0);
    }
    return date;
}
