<?php
	if ( $manual_automatic == 'manual' )
    {
?>
    <h3>Step 4. Import Finished</h3>

    <p>You're import has finished. Please see the log below:</p>

    <pre style="overflow:auto; max-height:450px; background:#FFF; border-top:1px solid #CCC; border-bottom:1px solid #CCC"><?php echo implode( "\n", $PH_Import->get_import_log() ); ?></pre>
<?php
    }
    elseif ( $manual_automatic == 'automatic' )
    {
?>
    <h3>Step 4. Import Successfully Setup</h3>

    <p>You've successfully setup your import. It will now run automatically at the frequency you specified.</p>

    <p>From the main 'Import Properties' screen you'll be able to edit and delete this import, as well as run it manually to test it.</p>

    <a href="<?php echo admin_url('admin.php?page=propertyhive_import_properties'); ?>" class="button">Return To Property Import Home</a>

<?php 
    }
?>