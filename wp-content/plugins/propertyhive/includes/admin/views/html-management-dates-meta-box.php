<?php
    $key_date_type_terms = get_terms( 'management_key_date_type', array(
        'hide_empty' => false,
        'parent' => 0
    ) );

    $recurrence_rules = get_option( 'propertyhive_key_date_type', array() );
    $recurrence_rules = is_array( $recurrence_rules ) ? $recurrence_rules : array();

    $parent_post_type = get_post_type( $post_id );

    $meta_query = array();

    switch ( $parent_post_type )
    {
        case 'property' :
        {
            $meta_query = array(
                array(
                    'key' => '_property_id',
                    'value' => $post_id,
                ),
                array(
                    'key' => '_tenancy_id',
                    'compare' => 'NOT EXISTS',
                ),
            );
            break;
        }
        case 'tenancy' :
        {
            $parent_property_id = get_post_meta( $post_id, '_property_id', true );
            $meta_query = array(
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_tenancy_id',
                        'value' => $post_id,
                    ),
                    array(
                        'key' => '_property_id',
                        'value' => $parent_property_id,
                    ),
                ),
            );
            break;
        }
    }

    if ( isset($selected_status) && !empty($selected_status) )
    {
        switch ( $selected_status )
        {
            case 'upcoming_and_overdue':
            {
                $meta_query[] = array(
                    'key' => '_key_date_status',
                    'value' => 'pending',
                );

                $upcoming_threshold = new DateTime('+ ' . apply_filters( 'propertyhive_key_date_upcoming_days', 7 ) . ' DAYS');
                $meta_query[] = array(
                    'key' => '_date_due',
                    'value' => $upcoming_threshold->format('Y-m-d'),
                    'type' => 'date',
                    'compare' => '<=',
                );
                break;
            }
            default:
            {
                $meta_query[] = array(
                    'key' => '_key_date_status',
                    'value' => $selected_status,
                );
                break;
            }
        }
    }

    if ( isset($selected_type_id) && !empty($selected_type_id) )
    {
        $meta_query[] = array(
            'key' => '_key_date_type_id',
            'value' => $selected_type_id,
        );
    }

    $key_dates = get_posts(array (
        'post_type' => 'key_date',
        'nopaging' => true,
        'meta_query' => $meta_query,
    ));
?>

<div class="tablenav top">

    <div class="alignleft actions">

    <select name="_key_date_type_id" id="_type_id_filter">
        <option value=""><?php echo __( 'All Types', 'propertyhive' ); ?></option>
        <?php
        if ( !empty( $key_date_type_terms ) && !is_wp_error( $key_date_type_terms ) )
        {
            foreach ($key_date_type_terms as $key_date_type_term)
            {
                $recurrence_type = isset($recurrence_rules[$key_date_type_term->term_id]) ? $recurrence_rules[$key_date_type_term->term_id]['recurrence_type'] : '';
                if ( $parent_post_type == 'tenancy' || ( $parent_post_type == 'property' && $recurrence_type == 'property_management' ) )
                {
                    $selected = ( isset($selected_type_id) && $selected_type_id == $key_date_type_term->term_id ) ? ' selected' : '';
                    echo '<option value="' . $key_date_type_term->term_id . '"' . $selected . '>' . $key_date_type_term->name . '</option>';
                }
            }
        }
        ?>
    </select>

    <select name="status" id="_date_status_filter">
        <option value="">All Statuses</option>
        <option value="upcoming_and_overdue" <?php echo ( isset($selected_status) && $selected_status == 'upcoming_and_overdue' ) ? 'selected' : ''; ?>>Upcoming & Overdue</option>
        <option value="booked" <?php echo ( isset($selected_status) && $selected_status == 'booked' ) ? 'selected' : ''; ?>> Booked</option>
        <option value="complete" <?php echo ( isset($selected_status) && $selected_status == 'complete' ) ? 'selected' : ''; ?>> Complete</option>
        <option value="pending" <?php echo ( isset($selected_status) && $selected_status == 'pending' ) ? 'selected' : ''; ?>> Pending</option>
    </select>

    <input type="button" name="filter_action" id="filter-key-dates-grid" class="button" value="Filter">

    </div>
    <div class="tablenav-pages one-page">
        <span class="displaying-num"><?php echo count($key_dates); ?> item<?php echo count($key_dates) != 1 ? 's' : ''; ?></span>
    </div>

    <br class="clear">

</div>

<h2 class="screen-reader-text">Posts list</h2>
<table class="wp-list-table widefat fixed striped table-view-list posts" style="border-collapse: collapse;">

    <thead>
        <tr>
            <th scope="col" id="description" class="manage-column column-description">
                Description
            </th>
            <th scope="col" id="notes" class="manage-column column-notes">
                Notes
            </th>
            <th scope="col" id="tenants" class="manage-column column-tenants">
                Tenants
            </th>
            <th scope="col" id="date_due" class="manage-column column-date_due">
                <span>Date Due</span>
            </th>
            <th scope="col" id="status" class="manage-column column-status">
                Status
            </th>
        </tr>
    </thead>

    <tbody id="the-list">
        <?php
        if ( count($key_dates) > 0 )
        {
            foreach ( $key_dates as $key_date_post )
            {
                $key_date = new PH_Key_Date( $key_date_post );
                ?>
                <tr id="post-<?php echo $key_date_post->ID; ?>" class="post-<?php echo $key_date_post->ID; ?> key-date-row">
                    <td class="description column-description" data-colname="Description">
                        <div class="cell-main-content"><?php echo $key_date->description(); ?></div>
                        <div class="row-actions">
                            <span class="inline hide-if-no-js">
                                <button type="button" id="<?php echo $key_date_post->ID; ?>" class="button-link meta-box-quick-edit">
                                    Quick&nbsp;Edit
                                </button>
                                 | 
                            </span>
                            <span class="trash">
                                <a href="" id="<?php echo $key_date_post->ID; ?>" class="submitdelete meta-box-delete">Delete</a>
                            </span>
                        </div>
                    </td>
                    <td class="notes column-notes" data-colname="Notes">
                        <div class="cell-main-content"><?php echo !empty($key_date->notes()) ? nl2br( $key_date->notes() ) : '-'; ?></div>
                        <div class="hidden hidden-key-date-notes"><?php echo $key_date->notes(); ?></div>
                    </td>
                    <td class="tenants column-tenants" data-colname="Tenants">
                        <div class="cell-main-content">
                        <?php
                            $tenants = '';
                            if ( !empty($key_date->tenancy_id) )
                            {
                                $tenancy = $key_date->tenancy();
                                $tenants = $tenancy->get_tenants(false, true);
                            }
                            echo !empty($tenants) ? $tenants : '-';
                        ?>
                        </div>
                    </td>
                    <td class="date_due column-date_due" data-colname="Date Due">
                        <?php
                            if ( $key_date->date_due()->format( 'H:i' ) == '00:00' )
                            {
                                $date_format = 'jS F Y';
                            }
                            else
                            {
                                $date_format = 'H:i jS F Y';
                            }
                        ?>
                        <div class="cell-main-content"><?php echo $key_date->date_due()->format( $date_format ); ?></div>
                    </td>
                    <td class="status column-status" data-colname="Status">
                        <div class="cell-main-content"><?php echo ucwords( $key_date->status() ); ?></div>
                        <div class="hidden hidden-date-type-id"><?php echo $key_date->key_date_type_id(); ?></div>
                    </td>
                </tr>
                <?php
            }
        }
        else
        {
            ?>
            <tr class="no-items">
                <td class="colspanchange" colspan="5">No key dates found</td>
            </tr>
            <?php
        }
        ?>
    </tbody>

    <tfoot>

        <tr>
            <th scope="col" class="manage-column column-description">
                Description
            </th>
            <th scope="col" class="manage-column column-notes">
                Notes
            </th>
            <th scope="col" class="manage-column column-tenants">
                Tenants
            </th>
            <th scope="col" class="manage-column column-date_due">
                <span>Date Due</span>
            </th>
            <th scope="col" class="manage-column column-status">
                Status
            </th>
        </tr>

    </tfoot>

</table>
<br>
<div class="propertyhive_meta_box">
    <div class="options_group">
        <p class="form-field _add_key_date_type_field">
            <label for="_add_key_date_type"><?php echo __('Key Date Type', 'propertyhive'); ?></label>
            <select id="_add_key_date_type" name="_add_key_date_type" class="select short">
                <option value="">Select Type</option>
                <?php
                if ( !empty( $key_date_type_terms ) && !is_wp_error( $key_date_type_terms ) )
                {
                    foreach ($key_date_type_terms as $key_date_type_term)
                    {
                        $recurrence_type = isset($recurrence_rules[$key_date_type_term->term_id]) ? $recurrence_rules[$key_date_type_term->term_id]['recurrence_type'] : '';
                        if ( $parent_post_type == 'tenancy' || ( $parent_post_type == 'property' && $recurrence_type == 'property_management' ) )
                        {
                            echo '<option value="' . $key_date_type_term->term_id . '">' . $key_date_type_term->name . '</option>';
                        }
                    }
                }
                ?>
            </select>
        </p>
        <p class="form-field _add_key_date_description_field">
            <label for="_add_key_date_description"><?php echo __('Description', 'propertyhive'); ?></label>
            <input type="text" id="_add_key_date_description" name="_add_key_date_description" value="" class="short">
        </p>
        <p class="form-field _add_key_date_due_field">
            <label for="_add_key_date_due"><?php echo __('Date Due', 'propertyhive'); ?></label>
            <input type="text" id="_add_key_date_due" name="_add_key_date_due" class="date-picker short" placeholder="yyyy-mm-dd" style="width:120px;" value="<?php echo date("Y-m-d"); ?>">

            <select id="_add_key_date_due_hours" name="_add_key_date_due_hours" class="select short" style="width:55px">';
                <?php
                for ( $i = 0; $i < 23; ++$i )
                {
                    $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                    echo '<option value="' . $j . '">' . $j . '</option>';
                }
                ?>
            </select>
            :
            <select id="_add_key_date_due_minutes" name="_add_key_date_due_minutes" class="select short" style="width:55px">
                <?php
                for ( $i = 0; $i < 60; $i+=5 )
                {
                    $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                    echo '<option value="' . $j . '">' . $j . '</option>';
                }
                ?>
            </select>
        </p>
        <p class="form-field _add_key_notes_field">
            <label for="_add_key_date_notes"><?php echo __('Notes', 'propertyhive'); ?></label>
            <textarea id="_add_key_date_notes" name="_add_key_date_notes" class="short"></textarea>
        </p>
        <p>
            <a href="#" class="add_key_date button"><?php _e( 'Add Key Date', 'propertyhive' ); ?></a>
        </p>
    </div>
</div>