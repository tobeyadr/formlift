<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! interface_exists('FormLift_Field_Interface') ){
	include_once dirname( __FILE__ ) . '/../lib/field-interface.php';
}
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2017-06-08
 * Time: 4:19 PM
 */
class FormLift_Field implements FormLift_Field_Interface
{
	var $id;
	var $ext;
	var $name;
	var $type;
	var $value = '';
	var $label;
	var $placeholder;
	var $options;
	var $date_options;
	var $required = false;
	var $auto_fill = false;
	var $formId;
	var $size;
	var $readonly;
	var $classes;
	var $advanced_options;
	var $pre_checked;

	function __construct( $options, $formId )
    {
        $this->type = $options['type'];
        if (isset($options['name']))
            $this->name = $options['name'];
        if (isset($options['id']))
            $this->id = $options['id'];
        if (isset($options['value']))
            $this->value = $options['value'];
        if (isset($options['label']))
            $this->label = $options['label'];
        if (isset($options['placeholder']))
            $this->placeholder = $options['placeholder'];
        
        /* as of 7.1 */
        if (isset($options['options']))
            $this->options = $options['options'];

        /* for compatibility */
        if (isset($options['radio_options']))
            $this->options = $options['radio_options'];
        if (isset($options['select_options']))
            $this->options = $options['select_options'];
        /* end compatibility */

        if (isset($options['date_options']))
            $this->date_options = $options['date_options'];
        
        global $FormLiftUser;

        if (isset($options['auto_fill']))
            $this->value = ( $FormLiftUser->get_user_data( $this->name ) )?$FormLiftUser->get_user_data( $this->name ):$this->value ;

        if (isset($options['required']))
            $this->required = true;
        if (isset($options['size']))
            $this->size = $options['size'];
        if (isset($options['pre_checked']))
            $this->pre_checked = $options['pre_checked'];

        if (isset($options['readonly']))
            $this->readonly = 'readonly';
        if (isset($options['classes']))
            $this->classes = $options['classes'];

        if (isset($options['advanced_options'] ) )
            $this->advanced_options = $options['advanced_options'];

        $this->formId = $formId;
        $this->ext = $formId . '-' . FormLift_Form::get_times_from_called();
    }

    public function getFormId()
    {
        return $this->formId;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function getType()
    {
	    return $this->type;
    }

    public function getName()
    {
        return ( isset( $this->name ) )? $this->name : $this->id;
    }

    public function getLabel()
    {
        if (isset($this->label))
            return $this->label;
        else 
            return $this->id;
    }

    public function getValue()
    {
    	return $this->value;
    }

    public function getSize()
    {
    	return $this->size;
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getUniqueId()
    {
    	return $this->id . "-" . $this->ext;
    }

    public function getOptions(){
    	return $this->options;
    }

    public function isReadOnly()
    {
    	return $this->readonly;
    }

    public function getAdditionalClasses()
    {
    	return $this->classes;
    }

    public function hidden()
    {
        return "<input type=\"hidden\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" value=\"{$this->getValue()}\" />";
    }

	public function text()
    {
        $placeholder = ( isset( $this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
        $label = ( !isset( $this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
        $input = "<input type=\"text\" class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder value=\"{$this->getValue()}\" {$this->isReadOnly()}/>";
        return $label.$input;
    }

    public function name()
    {
    	return $this->text();
    }

	public function email()
    {
        $placeholder = ( isset( $this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
        $label = ( !isset( $this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
        $input = "<input type=\"email\" class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder value=\"{$this->getValue()}\" {$this->isReadOnly()}/>";
        return $label.$input;
    }

	public function phone()
    {
        $placeholder = ( isset($this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
        $label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
        $input = "<input type=\"tel\" class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder value=\"{$this->getValue()}\" {$this->isReadOnly()}/>";
        return $label.$input;
    }

	public function zip_code()
	{
		$placeholder = ( isset($this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
		$label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
		$input = "<input type=\"text\" class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder value=\"{$this->getValue()}\" {$this->isReadOnly()} maxlength='5'/>";
		return $label.$input;
	}

	public function postal_code()
	{
		$placeholder = ( isset($this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
		$label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
		$input = "<input type=\"text\" class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder value=\"{$this->getValue()}\" {$this->isReadOnly()} maxlength='7'/>";
		return $label.$input;
	}

	public function website()
	{
		$placeholder = ( isset($this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
		$label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
		$input = "<input type=\"website\" class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder value=\"{$this->getValue()}\" {$this->isReadOnly()} />";
		return $label.$input;
	}

	public function number()
	{
		$placeholder = ( isset($this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
		$label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
		$input = "<input type=\"number\" class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder value=\"{$this->getValue()}\" {$this->isReadOnly()} />";
		return $label.$input;
	}

	public function password()
    {
        $placeholder = ( isset($this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
        $label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
        return "$label<input type=\"password\" class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder {$this->isReadOnly()}/>";
    }

	public function textarea()
    {
        $placeholder = ( isset($this->placeholder ) )? "placeholder=\"{$this->getLabel()}\"" : '';
        $label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
        return "$label<textarea class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder {$this->isReadOnly()}>{$this->getValue()}</textarea>";
    }

	public function checkbox()
    {
        global $FormLiftUser;

        $checked = '';

        if ($this->auto_fill && $FormLiftUser->get_user_data( $this->getName() ) || isset( $this->pre_checked ) ){
            $checked = "checked=\"checked\"";
        }
        $checkbox = "<input type=\"checkbox\" name=\"{$this->getName()}\" id=\"{$this->getUniqueId()}-special\" class=\"formlift_is_checkbox\" $checked value=\"{$this->getValue()}\" {$this->isReadOnly()}/>";
        $label = "<label class=\"formlift_label formlift_radio_label_container\" for=\"{$this->getUniqueId()}-special\"> {$this->getLabel()} $checkbox <span class=\"formlift_checkbox formlift_check_style\"></span></label>";
        return "<div id=\"{$this->getUniqueId()}\">$label</div>";
    }

	public function GDPR()
    {
	    $checkbox = "<input type=\"checkbox\" name=\"{$this->getName()}\" id=\"{$this->getUniqueId()}-special\" class=\"formlift_is_checkbox\" value=\"I Consent\"/>";
	    $label = "<label class=\"formlift_label formlift_radio_label_container\" for=\"{$this->getUniqueId()}-special\"> {$this->getLabel()} $checkbox <span class=\"formlift_checkbox formlift_check_style\"></span></label>";
	    return "<div id=\"{$this->getUniqueId()}\">$label</div>";
    }

	public function date()
	{
		$change_month = (!empty($this->date_options['show_month']))?$this->date_options['show_month']:'false';
		$change_year =  (!empty($this->date_options['show_year']))?$this->date_options['show_month']:'false';

		$minDate = formlift_convert_to_time_picker_usuable( $this->date_options['min_date'] );
		$maxDate = formlift_convert_to_time_picker_usuable( $this->date_options['max_date'] );

		$placeholder = ( isset($this->placeholder ) )? "placeholder='{$this->getLabel()}: YYYY-MM-DD'" : "placeholder=\"YYYY-MM-DD\"";
		$label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';
		$html = "$label<input class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" $placeholder value=\"{$this->getValue()}\" {$this->isReadOnly()}/>";
		$code = "<script>jQuery(document).ready(function (){ jQuery('#{$this->getUniqueId()}').datepicker({dateFormat: 'yy-mm-dd', changeMonth: $change_month, changeYear: $change_year, minDate: $minDate, maxDate: $maxDate});});</script>";

		return $code.$html;

	}

	public function radio()
    {
        $content = "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>";
        $content.= "<div class=\"infusion-radio\" id=\"{$this->getUniqueId()}\">";

        foreach ($this->options as $radio_id => $radio_option_list){

            $content.= "<div class=\"formlift_radio_option_container\">";
            // $name = $radio_option_list['name'];
            $id = $radio_id;
            $label = $radio_option_list['label'];
            $value = $radio_option_list['value'];

            $disabled = (isset($radio_option_list['disabled']))? 'disabled' : '';

            if ($value == $this->getValue() || ( isset( $this->pre_checked ) && $radio_id == $this->pre_checked ) ){
                $checked = "checked='checked'";
            } else {
                $checked = '';
            }

            $radio = "<input class=\"formlift_radio\" type=\"radio\" id=\"{$this->getUniqueId()}-{$id}\" name=\"{$this->getName()}\" value=\"$value\" {$checked} {$disabled} {$this->isReadOnly()}/>";
            $content.= "<label class=\"formlift_label formlift_radio_label_container\" for=\"{$this->getUniqueId()}-{$id}\"> $label $radio <span class=\"formlift_checkmark formlift_check_style\"></span></label>";
            $content.= "</div>";
        }

        $content.= "</div>";
        return $content;
    }

	public function select()
    {

        $label = ( !isset( $this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';

	    if ( $this->isReadOnly() ){
		    $content = $this->hidden();
		    $content.= "<select class=\"formlift_input\" disabled>";
	    } else {
		    $content = "<select class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\">";
	    }

        foreach ( $this->options as $option_num => $option_list ){
            $inside_label = $option_list['label'];
            $value = $option_list['value'];
            if ( empty( $value ) && isset( $this->placeholder ) )
                $inside_label = $this->getLabel();

            $disabled = (isset($option_list['disabled']))? 'disabled' : '';

            if ( $value == $this->getValue() || ( isset( $this->pre_checked ) && $option_num == $this->pre_checked ) ){
                $isselected = "selected";
            } else {
                $isselected = '';
            }

            $content.= "<option value=\"$value\" $disabled $isselected>$inside_label</option>";
        }

        $content.= "</select>";
        return $label.$content;
    }

	public function listbox()
    {
        $label = ( !isset($this->placeholder ) )? "<label class=\"formlift_label\" for=\"{$this->getUniqueId()}\">{$this->getLabel()}</label>" : '';

         if ( $this->isReadOnly() ){
            $content = $this->hidden();
            $content.= "<select class=\"formlift_input\" disabled multiple>";
        } else {
            $content = "<select class=\"formlift_input\" id=\"{$this->getUniqueId()}\" name=\"{$this->getName()}\" multiple>";
        }

        foreach ($this->options as $option_num => $option_list){
            $inside_label = $option_list['label'];
            $value = $option_list['value'];
            if (empty($value) && isset($this->placeholder))
                $inside_label = $this->getLabel();

            $disabled = (isset($option_list['disabled']))? 'disabled' : '';

            if ($value == $this->getValue()|| isset( $option_list['pre_checked'] ) ){
                $isSelected = "selected";
            } else {
                $isSelected = '';
            }

            $content.= "<option value=\"$value\" $disabled $isSelected>$inside_label</option>";
        }

        $content.= "</select>";
        return $label.$content;
    }

	public function button()
    {
        return "<div  id=\"{$this->getUniqueId()}\" class=\"formlift_button_container\"><button class=\"formlift_button\" type=\"submit\"/>{$this->getLabel()}</button></div>";
    }

	public function custom()
    {
        return do_shortcode( $this->getValue() );
    }

	public function template()
    {
        return apply_filters( "formlift_field_template_" . $this->getType(), "", $this );
    }

	public function error_code()
    {
    	$error_msg = apply_filters( 'formlift_field_preload_has_error_' . $this->getId(), false );

	    $class = ( empty( $error_msg) )? "formlift-no-error" : "";

        return "<div id=\"error-{$this->getUniqueId()}\" class=\"formlift-error-response {$class}\">{$error_msg}</div>";
    }

	public function __toString()
    {
        if ($this->getType() == 'hidden')
            return call_user_func( array( $this, $this->getType() ) );
        else {
            if ( method_exists( $this, $this->getType() ) ){
                $content = call_user_func( array( $this, $this->getType() ) );
            } else {
                $content = $this->template();
            }

            $content = apply_filters( 'formlift_field_inner_contents', $content, $this );

            return "<div class=\"formlift_field {$this->getFieldWidthClass()} {$this->getAdditionalClasses()}\">{$content}{$this->error_code()}</div>";
        }
    }

    private function getFieldWidthClass()
    {
        if ($this->getSize() == '1/2')
            return 'formlift-col formlift-span_1_of_2';
        elseif ($this->getSize() == '1/3')
            return 'formlift-col formlift-span_1_of_3';
        elseif ($this->getSize() == '2/3')
            return 'formlift-col formlift-span_2_of_3';
        elseif ($this->getSize() == '1/4')
            return 'formlift-col formlift-span_1_of_4';
        elseif ($this->getSize() == '3/4')
            return 'formlift-col formlift-span_3_of_4';
        else
            return 'formlift-col formlift-span_4_of_4';
    }

    public function get_decimal_size()
    {
        if ($this->getSize() == '1/2')
            return 50;
        elseif ($this->getSize() == '1/3')
            return 33;
        elseif ($this->getSize() == '2/3')
            return 66;
        elseif ($this->getSize() == '1/4')
            return 25;
        elseif ($this->getSize() == '3/4')
            return 75;
        else
            return 100;
    }
}