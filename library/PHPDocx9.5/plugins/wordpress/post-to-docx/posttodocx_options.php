<div id="posttodocx-options" class="posttodocx-option wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Post to DOCX Options</h2>

<p>For detailed documentation visit <a target="_blank" title="PHPDocX" rel="bookmark" href="http://www.phpdocx.com">PHPDocX</a>
</p>

<form method="post" action="options.php">
<?php settings_fields('posttodocx_options');
$posttodocx = get_option('posttodocx'); ?>
<h3>Options</h3>

<div class="posttodocx-option-body">
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Allowed Custom Post Types</th>
            <td>
                <?php
                $post_types = get_post_types(array('public'   => true),'names');
                foreach( $post_types as $post_type){ ?>
                    <input name="posttodocx[<?php echo $post_type; ?>]"
                           value="1" <?php echo ($posttodocx[$post_type]) ? 'checked="checked"' : ''; ?>
                           type="checkbox"/> <?php echo $post_type; ?><br/>
                <?php } ?>

                <p>Select post types for which you want to have DOCX download link.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Non Public Only</th>
            <td>
                <input name="posttodocx[nonPublic]"
                       value="1" <?php echo ($posttodocx['nonPublic']) ? 'checked="checked"' : ''; ?>
                       type="checkbox"/>

                <p>Select if you want to disable DOCX download link for public users. Only logged in users will be able to use DOCX download link in this case.</p>
            </td>
        </tr>

    </table>
</div>

<p class="submit">
    <input type="submit" class="button-primary" name="posttodocx[submit]" value="<?php _e('Save Changes') ?>"/>
</p>
</form>
</div>