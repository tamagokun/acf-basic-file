<?php

class acf_field_basic_file extends acf_field
{
    // vars
    var $settings, // will hold info such as dir / path
        $defaults; // will hold default field options
        
        
    /*
    *  __construct
    *
    *  Set name / label needed for actions / filters
    *
    *  @since	3.6
    *  @date	23/01/13
    */

    function __construct()
    {
        // vars
        $this->name = 'basic_file';
        $this->label = __('Basic File');
        $this->category = __("Content",'acf'); // Basic, Content, Choice, etc
        $this->defaults = array(
            'max_file_size' => ''
        );
        
        
        // do not delete!
        parent::__construct();
        
        
        // settings
        $this->settings = array(
            'path' => apply_filters('acf/helpers/get_path', __FILE__),
            'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
            'version' => '1.0.0',
            'uploaded' => array()
        );
        
        
        // actions
        add_action('acf/save_post', array($this, 'save_post'), 5);
        add_action('post_edit_form_tag', array($this, 'post_edit_form_tag'));
    }


    /*
    *  post_edit_form_tag
    *
    *  description
    *
    *  @type	function
    *  @date	18/06/13
    *
    *  @param	{int}	$post_id
    *  @return	{int}	$post_id
    */

    function post_edit_form_tag()
    {
        echo ' enctype="multipart/form-data"';
    }


    /*
    *  build_post_data
    *
    *  description
    *
    *  @type	function
    *  @date	19/06/13
    *
    *  @param	{int}	$post_id
    *  @return	{int}	$post_id
    */

    function build_post_data( &$post, $files )
    {
        foreach( $post as $k => $v )
        {
            // file exists?
            if( !isset( $files['name'][ $k ]))
            {
                continue;				
            }
            
            
            // create next level of file data
            $next = array();
            $next['name'] = $files['name'][ $k ];
            $next['type'] = $files['type'][ $k ];
            $next['tmp_name'] = $files['tmp_name'][ $k ];
            $next['error'] = $files['error'][ $k ];
            $next['size'] = $files['size'][ $k ];
            
            
            if( is_array( $v ) )
            {
                // walk $files one step into the array
                $this->build_post_data( $post[ $k ], $next );
            }
            elseif( empty($v) )
            {
                // no data in $_POST, but there will be data in $_FILES
                $post[ $k ] = $next;
            }
        }
    }


    /*
    *  save_post
    *
    *  description
    *
    *  @type	function
    *  @date	19/06/13
    *
    *  @param	{int}	$post_id
    *  @return	{int}	$post_id
    */

    function save_post( $post_id )
    {
        // validate
        if( !isset($_FILES['fields']['error']) || empty($_FILES['fields']['error']) )
        {
            return;
        }
        
        
        // build $_POST data
        $this->build_post_data( $_POST['fields'], $_FILES['fields'] );
        
        
        // unset $_FILES
        unset( $_FILES['fields'] );
    }


    /*
    *  create_options()
    *
    *  Create extra options for your field. This is rendered when editing a field.
    *  The value of $field['name'] can be used (like bellow) to save extra data to the $field
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$field	- an array holding all the field's data
    */

    function create_options( $field )
    {
        // key
        $key = $field['name'];
        
        
        ?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
    <td class="label">
        <label><?php _e("Max File Size",'acf'); ?></label>
        <p class="description"><?php _e("in KB",'acf'); ?></p>
    </td>
    <td>
        <?php
        
        do_action('acf/create_field', array(
            'type'      =>  'number',
            'name'      =>  'fields['.$key.'][max_file_size]',
            'value'     =>  $field['max_file_size'],
        ));
        
        ?>
    </td>
</tr>
        <?php
        
    }


    /*
    *  create_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param	$field - an array holding all the field's data
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    */

    function create_field( $field )
    {
        // vars
        $o = array(
            'error'		=>	'',
            'class'		=>	'',
            'icon'		=>	'',
            'title'		=>	'',
            'size'		=>	'',
            'url'		=>	'',
            'name'		=>	'',
        );
        
        if( $field['value'] && is_numeric($field['value']) )
        {
            $file = get_post( $field['value'] );
            
            
            if( $file )
            {
                $o['class'] = 'active';
                $o['icon'] = wp_mime_type_icon( $file->ID );
                $o['title']	= $file->post_title;
                $o['size'] = @size_format(filesize( get_attached_file( $file->ID ) ));
                $o['url'] = wp_get_attachment_url( $file->ID );
                $o['name'] = end(explode('/', $o['url']));				
            }
        }
        elseif( $field['value'] )
        {

            $o['error'] = $field['value'];
            $field['value'] = false;
            
        }
        
        
        ?>
<div class="acf-file-uploader clearfix <?php echo $o['class']; ?>" >
    <input class="acf-file-value" type="hidden" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" />
    <div class="has-file">
        <ul class="hl clearfix">
            <li>
                <img class="acf-file-icon" src="<?php echo $o['icon']; ?>" alt=""/>
            </li>
            <li>
                <p>
                    <strong class="acf-file-title"><?php echo $o['title']; ?></strong>
                </p>
                <p>
                    <strong>Name:</strong>
                    <a class="acf-file-name" href="<?php echo $o['url']; ?>" target="_blank"><?php echo $o['name']; ?></a>
                </p>
                <p>
                    <strong>Size:</strong>
                    <span class="acf-file-size"><?php echo $o['size']; ?></span>
                </p>
            </li>
        </ul>
    </div>
    <div class="no-file">
        
        <?php if( $o['error'] ): ?>
            <p><?php echo $o['error']; ?></p>
        <?php endif; ?>
        
        <input type="file" name="<?php echo $field['name']; ?>" id="<?php echo $field['id']; ?>" />

    </div>
    <script>
        // fix enctype for form
        jQuery("<?php echo $field['id']; ?>").parents("form").attr("enctype", "multipart/form-data");

        // fix validation
        jQuery(document).on('acf/validate_field', function( e, field ){

            var $field = jQuery(field);

            if( $field.find('input[type="file"]').exists() ) {
                $field.data('validation', true);

                if( $field.find('input[type="file"]').val() == '' ) {
                    $field.data('validation', false);
                }
            }

        });
    </script>
</div>
        <?php
    }



    function update_value( $value, $post_id, $field )
    {
        
        // only upload if new data is available
        if( !is_array($value) )
        {
            return $value;	
        }
        
        
        // already updateded?
        // ACF saves twice (revision + post)
        if( !empty($this->settings['uploaded']) )
        {
            foreach( $this->settings['uploaded'] as $v )
            {
                if( $value['tmp_name'] == $v['tmp_name'] )
                {
                    return $v['post_id'];
                }
            }
        }
        
        
        // required
        require_once( ABSPATH . "/wp-load.php" );
        require_once( ABSPATH . "/wp-admin/includes/file.php" );
        require_once( ABSPATH . "/wp-admin/includes/image.php" );
            
            
        // required for wp_handle_upload() to upload the file
        $upload_overrides = array( 'test_form' => FALSE );
        
        
        // load up a variable with the upload direcotry
        $uploads = wp_upload_dir();
        

        // vars
        if( !$field['max_file_size'] )
        {
            $field['max_file_size'] = intval( ini_get('upload_max_filesize') ) * 1024;
        }
        
        
        // kb to b
        $field['max_file_size'] = intval( $field['max_file_size'] ) * 1024;
        
        
        // is size correct?
        if( $value['size'] > $field['max_file_size'] )
        {
            return 'Error: File size is too large. Max upload size is ' . size_format($field['max_file_size']);
        }
        
        
        // upload
        $upload = wp_handle_upload( $value, $upload_overrides );
        
        
        // upload error
        if( is_wp_error($upload) )
        {
            return $upload->errors['wp_upload_error'][0];
        }
        
        
        // vars
        $filetype   = wp_check_filetype(basename($upload['file']), null);
        $title      = preg_replace('/\.[^.]+$/', '', $value['name'] );

        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => $title,
            'post_parent' => $post_id,
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
                
        $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
        
        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
        
        wp_update_attachment_metadata( $attach_id, $attach_data );
            
        
        // add to uploaded
        $value['post_id'] = $attach_id;
        
        $this->settings['uploaded'][] = $value;
        
        
        // return new ID to save to postmeta
        return $attach_id;
    }


}


// create field
new acf_field_basic_file();

?>
