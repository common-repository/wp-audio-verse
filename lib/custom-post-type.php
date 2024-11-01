<?php
//
/**
 * Customize cmb - JW Post Types
 * thanks - Jeffrey Way
 */
class Xtl_Post_Type
{

    /**
     * The name of the post type.
     * @var string
     */
    public $post_type_name;

    /**
     * A list of user-specific options for the post type.
     * @var array
     */
    public $post_type_args;


    /**
     * Sets default values, registers the passed post type, and
     * listens for when the post is saved.
     *
     * @param string $name The name of the desired post type.
     * @param array @post_type_args Override the options.
     */
    function __construct($name, $post_type_args = array())
    {


        $this->post_type_name = strtolower($name);
        $this->post_type_args = (array)$post_type_args;

        // First step, register that new post type
        $this->init(array(&$this, "register_post_type"));
        //$this->save_post();
        add_action('save_post', array($this, 'xtl_save_the_value') );

        
    }

    /**
     * Helper method, that attaches a passed function to the 'init' WP action
     * @param function $cb Passed callback function.
     */
    function init($cb)
    {
        add_action("init", $cb);
    }

    /**
     * Helper method, that attaches a passed function to the 'admin_init' WP action
     * @param function $cb Passed callback function.
     */
    function admin_init($cb)
    {
        add_action("admin_init", $cb);

    }


    /**
     * Registers a new post type in the WP db.
     */
    function register_post_type()
    {
        $n = ucwords($this->post_type_name);

        $args = array(
            "label" => $n . 's',
            'singular_name' => $n,
            "public" => true,
            "publicly_queryable" => true,
            "query_var" => true,
            #"menu_icon" => get_stylesheet_directory_uri() . "/article16.png",
            "rewrite" => true,
            "capability_type" => "post",
            "hierarchical" => false,
            "menu_position" => null,
            "supports" => array("title", "editor", "thumbnail"),
            'has_archive' => true
            );

        // Take user provided options, and override the defaults.
        $args = array_merge($args, $this->post_type_args);

        register_post_type($this->post_type_name, $args);
    }


    /**
     * Registers a new taxonomy, associated with the instantiated post type.
     *
     * @param string $taxonomy_name The name of the desired taxonomy
     * @param string $plural The plural form of the taxonomy name. (Optional)
     * @param array $options A list of overrides
     */
    function add_taxonomy($taxonomy_name, $plural = '', $options = array())
    {
        // Create local reference so we can pass it to the init cb.
        $post_type_name = $this->post_type_name;

        // If no plural form of the taxonomy was provided, do a crappy fix. :)

        if (empty($plural)) {
            $plural = $taxonomy_name . 's';
        }

        // Taxonomies need to be lowercase, but displaying them will look better this way...
        $taxonomy_name = ucwords($taxonomy_name);

        // At WordPress' init, register the taxonomy
        $this->init(
            function() use($taxonomy_name, $plural, $post_type_name, $options)
            {
                // Override defaults with user provided options

                $options = array_merge(
                    array(
                     "hierarchical" => false,
                     "label" => $taxonomy_name,
                     "singular_label" => $plural,
                     "show_ui" => true,
                     "query_var" => true,
                     "rewrite" => array("slug" => strtolower($taxonomy_name))
                     ),
                    $options
                    );

                // name of taxonomy, associated post type, options
                $taxonomy_name = str_replace(' ', '-', $taxonomy_name);
                register_taxonomy(strtolower($taxonomy_name), $post_type_name, $options);
            });
    }


    /**
     * Creates a new custom meta box in the New 'post_type' page.
     *
     * @param string $title
     * @param array $form_fields Associated array that contains the label of the input, and the desired input type. 'Title' => 'text'
     */
    function add_meta_box($title, $form_fields = array()) {

        $post_type_name = $this->post_type_name;

        // end update_edit_form
        add_action('post_edit_form_tag', function()
        {
            echo ' enctype="multipart/form-data"';
        });


        // At WordPress' admin_init action, add any applicable metaboxes.
        $this->admin_init(function() use($title, $form_fields, $post_type_name) {

            add_meta_box(
                    strtolower(str_replace(' ', '_', $title)), // id
                    $title, // title
                    function($post, $data) { // function that displays the form fields
                        global $post;

                        wp_nonce_field(plugin_basename(__FILE__), 'jw_nonce');

                        // List of all the specified form fields
                        $inputs = $data['args'][0];


                        // Get the saved field values
                        $meta = get_post_custom($post->ID);

                        // For each form field specified, we need to create the necessary markup
                        // $name = Label, $type = the type of input to create
                        foreach ($inputs as $name => $type) {
                            #'Happiness Info' in 'Snippet Info' box becomes
                            # snippet_info_happiness_level
                            $id_name = $data['id'] . '_' . strtolower(str_replace(' ', '_', $name));


                            if (is_array($inputs[$name])) {
                                // then it must be a select or file upload
                                // $inputs[$name][0] = type of input

                                if (strtolower($inputs[$name][0]) === 'select') {
                                    // filter through them, and create options
                                    $select = "<select name='$id_name' class='widefat'>";
                                    foreach ($inputs[$name][1] as $option) {
                                        // if what's stored in the db is equal to the
                                        // current value in the foreach, that should
                                        // be the selected one

                                        if (isset($meta[$id_name]) && $meta[$id_name][0] == $option) {
                                            $set_selected = "selected='selected'";
                                        } else $set_selected = '';

                                        $select .= "<option value='$option' $set_selected> $option </option>";
                                    }
                                    $select .= "</select>";

                                }
                            }

                            // Attempt to set the value of the input, based on what's saved in the db.
                            $value = isset($meta[$id_name][0]) ? $meta[$id_name][0] : '';

                            $checked = ($type == 'checkbox' && !empty($value) ? 'checked' : '');


                            // TODO - Add the other input types.
                            $lookup = array(

                                "text" => "<input class='widefat' type='text' name='$id_name' value='$value' class='widefat' />",
                                "textarea" => "<textarea class='widefat' name='$id_name' cols='30' rows='10'>$value</textarea>",
                                "editor" => "",
                                "checkbox" => "<input class='widefat' type='checkbox' name='$id_name' value='$name' $checked />",
                                "select" => isset($select) ? $select : '',
                                "file" => "<input class='widefat button' type='file' name='$id_name' id='$id_name' />",
                                "upload" => "<input class='widefat' type='text' name='$id_name' id='$id_name' value='$value' /><a href=\"#\" class=\"dfmr-upload-external-file button\">Upload</a>"
                                );


                                ?>

                                <span>
                                    <label><?php echo ucwords($name); ?></label> <br>
                                    <?php echo $lookup[is_array($type) ? $type[0] : $type]; ?>

                                    <br>


                                    <?php

                                    if( $type === 'editor') {
                                        wp_editor( $value, $id_name, $settings = array(
                                            'textarea_name' => $id_name,
                                            'media_buttons' =>      false,
                                            'tinymce'       => true,
                                            'textarea_rows' => 6
                                            ) 
                                        );
                                    }

                                 // If a file was uploaded, display it below the input.
                                    $file = get_post_meta($post->ID, $id_name, true);
                                    if ( $type === 'upload') {
                                        $upload = get_post_meta( $post->ID, $id_name, true );
                                        //echo "<img src='$upload' alt='' style='max-width: 400px;' />";
                                        ?>
                                        <script>
                                            !function($){$(".dfmr-upload-external-file").click(function(e){e.preventDefault();var $el=$(this).parent(),uploader=wp.media({multiple:!1}).on("select",function(){var selection=uploader.state().get("selection"),attachment=selection.first().toJSON();$("input",$el).val(attachment.url),$("img",$el).attr("src",attachment.url).show()}).open()})}(jQuery);
                                        </script>
                                        <?php
                                    }

                                    if ( $type === 'file' ) {
                                        // display the image
                                        $file = get_post_meta($post->ID, $id_name, true);

                                        $file_type = wp_check_filetype($file);
                                        $image_types = array('jpeg', 'jpg', 'bmp', 'gif', 'png');
                                        if ( isset($file) ) {
                                            if ( in_array($file_type['ext'], $image_types) ) {
                                                echo "<img src='$file' alt='' style='max-width: 400px;' />";
                                            } else {
                                                echo "<a href='$file'>$file</a>";
                                            }
                                        }
                                    }
                                    ?>
                                </span>

                                <?php

                            }
                        },
                    $post_type_name, // associated post type
                    'normal', // location/context. normal, side, etc.
                    'default', // priority level
                    array($form_fields) // optional passed arguments.
                ); // end add_meta_box
});
}


    /**
     * When a post saved/updated in the database, this methods updates the meta box params in the db as well.
     */
    public function xtl_save_the_value($post_id) {

                // Only do the following if we physically submit the form,
                // and now when autosave occurs.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        global $post;

        $nonce = '';
        if(isset($_POST['jw_nonce']))
            $nonce = $_POST['jw_nonce'];

        if ($_POST && !wp_verify_nonce($nonce, plugin_basename(__FILE__))) {
            return;
        }

        if (isset($_POST['verse_settings_verse_translate'])) {
            update_post_meta($post_id, 'verse_settings_verse_translate', sanitize_text_field($_POST['verse_settings_verse_translate']));
        }

        if (isset($_POST['verse_settings_upload_mp3'])) {
            update_post_meta($post_id, 'verse_settings_upload_mp3', sanitize_text_field($_POST['verse_settings_upload_mp3']));
        }

        if (isset($_POST['verse_advance_settings_rtl_main_content'])) {
            update_post_meta($post_id, 'verse_advance_settings_rtl_main_content', sanitize_text_field($_POST['verse_advance_settings_rtl_main_content']));
        }

        if (isset($_POST['verse_advance_settings_rtl_translated_content'])) {
            update_post_meta($post_id, 'verse_advance_settings_rtl_translated_content', sanitize_text_field($_POST['verse_advance_settings_rtl_translated_content']));
        }


        if (isset($_POST['verse_advance_settings_play_autoplay'])) {
            update_post_meta($post_id, 'verse_advance_settings_play_autoplay', sanitize_text_field($_POST['verse_advance_settings_play_autoplay']));
        }

        
        if (isset($_POST['verse_advance_settings_player_below_content'])) {
            update_post_meta($post_id, 'verse_advance_settings_player_below_content', sanitize_text_field($_POST['verse_advance_settings_player_below_content']));
        }
        

    }
}

