<?phpif ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directlyclass FormLift_Edit_Box{    var $ID;    var $form;    function __construct( $post_id )    {        $this->ID = $post_id;        $this->form = new FormLift_Form( $post_id );    }    /**     * display the initial page for the HTML editor meta box     */    function create_page()    {        wp_nonce_field( 'formlift_saving_form_fields', 'formlift_editor_nonce');        ?>        <div class="editor-header">            <div class="header-item">                <label for="form_shortcode_area"></label><input class="formlift-input" id="form_shortcode_area" type="text" value='[formlift id="<?php echo $this->ID ?>"]' readonly/>            </div>            <div class="header-item">                <button type="button" class="button button-primary" onclick="copy_shortcode('#form_shortcode_area')">COPY SHORTCODE</button>            </div>            <div class="header-item">                <a class="button formlift_trigger_popup" id="editor-add-custom-field"  title="Add A Custom Field" href="#source_id=custom-field-options">ADD CUSTOM FORM FIELD</a>            </div>        </div>        <style>            #postdivrich{                display: none;            }        </style>	    <?php echo new FormLift_Edit_PopUp(); ?>	    <?php wp_enqueue_editor(); ?>        <script>            var ThisFormID =  <?php echo $this->ID?>;            <?php $fields = get_post_meta( $this->ID, FORMLIFT_FIELDS, true );?>            var formliftInfusionForm = <?php echo json_encode($fields)?>;            jQuery(document).ready( function(){                FormLiftEditor.init(formliftInfusionForm);            });        </script>        <?php print_r( $fields ); ?>        <style>            .ui-state-highlight {                display: inline-block;                border: 2px #777777 dashed;                background: #ffffff;                margin: 0 0 1% 1%;            }        </style>        <?php self::get_custom_field_options() ?>        <?php do_action( 'pre_formlift_editor_load', $this->ID ) ?>        <div class="formlift-sortable-fields formlift-group" id="formlift-field-editor">		    <?php		    if ( is_array( $fields ) ){			    foreach ($fields as $field_id => $field_options){				    $field = new FormLift_Field_Editor( $field_options );				    echo $field;			    }		    } elseif ( formlift_is_connected() ) {			    ?>                <div style="padding: 20px 0 20px 20px">                    <select title="Form Selector" id="infusionsoft_form_id" name="formlift_form_settings[infusionsoft_form_id]" style="max-width:300px;margin-right: 20px;">					    <?php					    $webforms = formlift_get_infusionsoft_webforms();					    foreach ($webforms as $id => $name) {						    echo "<option value='$id'>$name</option>";					    }					    ?>                    </select>                    <input type="submit" name="formlift_form_settings[form_refresh]" value="Import Form Code" class="button-primary">                    <input type="submit" name="formlift_form_settings[formlift_update_webform_list]" value="Refresh Webform List" class="button-primary">                    <div class="formlift-error" style="padding:20px;"> Or use form code. </div>				    <?php echo new FormLift_Setting_Field(FORMLIFT_TEXT, 'infusionsoft_form_original_html', 'Insert Form Html'); ?>				    <?php echo new FormLift_Setting_Field(FORMLIFT_BUTTON, 'parse_original_html', 'Import From Html', "DO IMPORT" );?>                </div>			    <?php		    } else {			    ?>                <div class="formlift-error" style="padding:20px;">You must connect to the Infusionsoft API first to import web forms. <a href="<?php echo admin_url('edit.php?post_type=infusion_form&page=formlift_settings_page'); ?>">Do that in the settings</a></div>                <div class="formlift-error" style="padding:20px;"> Or use form code. </div>			    <?php echo new FormLift_Setting_Field(FORMLIFT_TEXT, 'infusionsoft_form_original_html', 'Insert Form Html'); ?>			    <?php echo new FormLift_Setting_Field(FORMLIFT_BUTTON, 'parse_original_html', 'Import From Html', "DO IMPORT" ); ?>			    <?php		    }		    ?>        </div>        <?php    }    public static function get_custom_field_options()    {	    $categories = get_formlift_field_types();        ?>        <div style="display:none" id="custom-field-options" >            <?php            foreach ($categories as $type_category => $types ):                ?>            <div>                <h1><?php echo $type_category?> Fields:</h1>                <?php                foreach ($types as $type_id => $type_name ):            ?>            <a class="add-custom-field" href="#type=<?php echo $type_id?>">                <div class="custom-field-type-choice">                    <?php echo $type_name ?>                </div>            </a>                <?php endforeach; ?>            </div>            <?php endforeach; ?>        </div>        <?php    }    public static function get_field_html()    {        $options = json_decode( stripslashes( $_POST['options'] ), true );        //$field_editor = apply_filters( 'formlift_field_editor_class', 'FormLift_Field_Editor' );        $field = new FormLift_Field_Editor( $options );        wp_die( "{$field}" );    }    public static function get_option_html()    {        $id = $_POST['option_id'];        $field_id = $_POST['field_id'];         $option_key = FORMLIFT_FIELDS;        $row = "<div class=\"formlift-option-editor\" id=\"$id-$field_id\" data-field-id=\"$field_id\">";        $row.= "<input type=\"text\" name=\"{$option_key}[{$field_id}][options][{$id}][label]\" value=\"\">";        $row.= "<input type=\"text\" name=\"{$option_key}[{$field_id}][options][{$id}][value]\" value=\"\">";        $row.= "<input type=\"radio\" name=\"{$option_key}[{$field_id}][pre_checked]\" value=\"{$id}\">Selected";        $row.= "<input type=\"checkbox\" name=\"{$option_key}[{$field_id}][options][{$id}][disabled]\" value=\"1\">Disabled";        $row.= "<span class=\"dashicons dashicons-plus formlift-option-add formlift-option-icon\"></span><span class=\"dashicons dashicons-trash formlift-option-icon formlift-option-delete\"></span><span class=\"dashicons dashicons-move formlift-move-icon formlift-option-icon\"></span>";        $row.= "</div>";        wp_die($row);    }    public static function add_meta_box()    {        add_meta_box(            "infusion_meta",            "Infusionsoft Form",            array('FormLift_Edit_Box', "meta_box_call_back"),            "infusion_form",            "normal",            "high"        );    }    public static function meta_box_call_back( $post )    {        $meta_box = new FormLift_Edit_Box( $post->ID );        $meta_box->create_page();    }    public static function add_scripts()    {        $screen = get_current_screen();        if ( $screen->post_type !== 'infusion_form' )        return;        wp_enqueue_script( 'formlift-copy', plugins_url( 'assets/js/copy-shortcode.js', __FILE__ ), array(), FORMLIFT_JS_VERSION );        if ( $screen->post_type !== 'infusion_form' ||  $screen->base !== 'post' ){            return;        }        wp_enqueue_style( 'formlift-grid', plugins_url( 'assets/css/responsive-grid-framework.css', __FILE__ ), array(), FORMLIFT_CSS_VERSION );        wp_enqueue_style( 'formlift-editor', plugins_url( 'assets/css/editor.css', __FILE__ ), array(), FORMLIFT_CSS_VERSION );        wp_enqueue_style( 'wp-color-picker');        wp_enqueue_style( 'formlift-admin' );        wp_enqueue_style( 'formlift-settings' );        wp_enqueue_script( 'jQuery' );        wp_enqueue_script( 'wp-color-picker-alpha' );        wp_enqueue_script( 'formlift-admin' );        wp_enqueue_script( 'formlift-editor', plugins_url( 'assets/js/editor.js', __FILE__ ), array(), FORMLIFT_JS_VERSION, true );    }}add_action( 'wp_ajax_formlift_get_field_html', array('FormLift_Edit_Box', 'get_field_html' ));add_action( 'wp_ajax_formlift_get_option_html', array('FormLift_Edit_Box', 'get_option_html' ));add_action( 'admin_enqueue_scripts', array('FormLift_Edit_Box', 'add_scripts') );add_action( 'add_meta_boxes' , array( 'FormLift_Edit_Box', 'add_meta_box' ) );