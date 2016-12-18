<?php

/**
 * Class for creating input field element HTML
 *
 * @package Caldera_Forms
 * @author    Josh Pollock <Josh@CalderaWP.com>
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 CalderaWP LLC
 */
class Caldera_Forms_Field_Input extends Caldera_Forms_Field_HTML{

	/**
	 * @inheritdoc
	 */
	public static function html( array $field, array $field_structure, array $form ){
		$type = Caldera_Forms_Field_Util::get_type( $field );
		$field_base_id = Caldera_Forms_Field_Util::get_base_id( $field, null, $form );
		$default = self::find_default( $field );

		$sync = false;
		if( in_array( $type, self::sync_fields() ) ){
			$syncer = Caldera_Forms_Sync_Factory::get_object( $form, $field, $field_base_id );
			$sync = $syncer->can_sync();
			$default = $syncer->get_default();
		}

		if( 'text' == $type && !empty( $field['config']['type_override'] ) ){
			$type = $field['config']['type_override'];
		}
		$required = '';

		$field_classes = Caldera_Forms_Field_Util::prepare_field_classes( $field, $form );
		$mask = self::get_mask_string( $field );
		$place_holder = self::place_holder_string( $field );
		$default = Caldera_Forms::do_magic_tags( $default, null, $form );
		$attrs = array(
			'type' => $type,
			'data-field' =>$field[ 'ID'],
			'class' => $field_classes[ 'field' ],
			'id' => $field_base_id,
			'name' => $field_structure['name'],
			'value' => $default,
			'data-type' => $type
		);

		if( 'number' == $type ){
			foreach( array(
				'min',
				'max',
				'step'
			) as $index ){
				if( isset( $field[ 'config' ][ $index ] ) ){
					$attrs[ $index ] = $field[ 'config' ][ $index ];
				}
			}
		}elseif ( 'phone_better' == $type ){
			$attrs[ 'type' ] = 'tel';
		}elseif ( 'credit_card_number' == $type ){
			$attrs[ 'type' ] = 'tel';
			$attrs[ 'class' ][] = 'cf-credit-card ';
			$attr[ 'data-parsley-creditcard' ] = Caldera_Forms_Field_Util::credit_card_types( $field, $form );
		}elseif( 'credit_card_exp' == $type ){
			$attrs[ 'type' ] = 'tel';
			$attr[ 'data-parsley-creditcard' ] = '';
		}elseif ( 'credit_card_cvv' == $type ){
			$attrs[ 'type' ] = 'tel';
			$attr[ 'data-parsley-creditcard' ] = '';
		}

		if( $field_structure['field_required'] ){
			$required = 'required';
			$attrs[ 'aria-required' ] = 'true';
		}

		if( $sync ){
			$attrs[ 'data-binds' ] = wp_json_encode( $syncer->get_binds() );
			$attrs[ 'data-sync' ] = $default;
		}

		$attr_string = caldera_forms_field_attributes(
			$attrs,
			$field,
			$form
		);

		$aria = self::aria_string( $field_structure );

		return '<input ' .  $place_holder . ' ' . $mask . ' ' .  $required . ' ' . $attr_string   . ' ' . $aria .' >';

	}

	/**
	 * Defined which fields use sync
	 *
	 * @sine 1.5.0
	 *
	 * @return array
	 */
	protected static function sync_fields(){
		return array(
			'text',
			'email',
			'html',
			'number',
			'hidden',
			'url',
			'phone_better',
			'paragraph'Sync should support URL, new phone and paragraph fields #1034
		);
	}


	/**
	 * Get input mask config string
	 *
	 * @since 1.5.0
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	protected static function get_mask_string( array  $field ){
		$mask = '';
		if ( 'phone' != Caldera_Forms_Field_Util::get_type( $field ) ) {
			if ( ! empty( $field[ 'config' ][ 'masked' ] ) ) {
				$mask = "data-inputmask=\"'mask': '" . $field[ 'config' ][ 'mask' ] . "'\" ";
			}
		} else {
			$mask = '(999)999-9999';
			if( $field['config']['type'] == 'international' ){
				$mask = '+99 99 999 9999';
			}elseif ( $field['config']['type'] == 'custom' ) {
				$mask = $field['config']['custom'];
			}
		}

		return $mask;
	}


}