<?php
	if ( !function_exists( 'acme_fbuild__checkbox' ) ) {
		add_filter( 'acme_fbuild__checkbox', 'acme_fbuild__checkbox', 10, 1 );
		/**
		 * HTML Checkbox Parser
		 * VERSION 1.0
		 * @param $args
		 *
		 * @return string
		 */
		function acme_fbuild__checkbox ( $args ) {
			$defaults = array (
				'label'    => 'acme_generic_label',
				'name'     => 'acme_generic_field_name',
				'value'    => null,
				'basename' => 'acme_fieldset',
				'class'    => array ( 'acme_generic_form_class' )
			);
			$opts = array_merge( $defaults, $args );
			$pattern = is_array( $opts['name'] ) ? '%s[%s]' : '%s-%s';
			$name = is_array( $opts['name'] ) ? reset( $opts['name'] ) : $opts['name'];
			$attrs = null;
			if ( isset( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				$attrs = apply_filters( 'acme_fbuild__custom_attrs', $attrs, $args['custom_attributes'] );
			}
			
			$res = sprintf( '<legend class="screen-reader-text"><span>%s</span></legend>', $opts['label'] );
			$res .= sprintf( '<label for="%s"><span>%s:</span></label>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				$opts['label']
			);
			$res .= sprintf( '<input type="checkbox" id="%1$s" name="%2$s" value=1 %3$s %4$s>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				sprintf( $pattern, $opts['basename'], $name ),
				checked( $opts['value'], 1, false ),
				$attrs
			);
			
			return $res;
		}
	}
	if ( !function_exists( 'acme_fbuild__radio' ) ) {
		add_filter( 'acme_fbuild__radio', 'acme_fbuild__radio', 10, 1 );
		/**
		 * HTML Checkbox Parser
		 * VERSION 1.0
		 * @param $args
		 *
		 * @return string
		 */
		function acme_fbuild__radio ( $args ) {
			$defaults = array (
				'label'    => 'acme_generic_label',
				'name'     => 'acme_generic_field_name',
				'value'    => null,
				'basename' => 'acme_fieldset',
				'show_colon' => true
			);
			$opts = array_merge( $defaults, $args );
			$pattern = is_array( $opts['name'] ) ? '%s[%s]' : '%s-%s';
			$name = is_array( $opts['name'] ) ? reset( $opts['name'] ) : $opts['name'];
			$attrs = null;
			if ( isset( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				$attrs = apply_filters( 'acme_fbuild__custom_attrs', $attrs, $args['custom_attributes'] );
			}
			
			$res = sprintf( '<legend class="screen-reader-text"><span>%s</span></legend>', $opts['label'] );
			$res .= sprintf( '<input type="radio" id="%1$s" name="%2$s" value="%5$s" %3$s %4$s>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				sprintf( $pattern, $opts['basename'], $name ),
				checked( $opts['value'], $opts['compare'], false ),
				$attrs,
				$opts['value']
			);
			$res .= sprintf( '<label for="%s">%s%s</label>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				$opts['label'],
				$opts['show_colon'] ? ':' : null
			);
			
			return $res;
		}
	}
	
	if ( !function_exists( 'acme_fbuild__hidden' ) ) {
		add_filter( 'acme_fbuild__hidden', 'acme_fbuild__hidden', 10, 1 );
		/**
		 * HTML Input TEXT Parser
		 * VERSION 1.0		 *
		 * @param $args
		 *
		 * @return string
		 */
		function acme_fbuild__hidden ( $args ) {
			$defaults = array (
				'name'     => 'acme_generic_field_name',
				'value'    => null,
				'basename' => 'acme_fieldset'
			);
			
			$opts = array_merge( $defaults, $args );
			$pattern = is_array( $opts['name'] ) ? '%s[%s]' : '%s-%s';
			$name = is_array( $opts['name'] ) ? reset( $opts['name'] ) : $opts['name'];
			$attrs = null;
			if ( isset( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				$attrs = apply_filters( 'acme_fbuild__custom_attrs', $attrs, $args['custom_attributes'] );
			}
			
			$res = sprintf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$s" %4$s>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				sprintf( $pattern, $opts['basename'], $name ),
				empty( $opts['value'] ) ? '' : $opts['value'],
				$attrs
			);
			
			return $res;
		}
	}
	if ( !function_exists( 'acme_fbuild__text' ) ) {
		add_filter( 'acme_fbuild__text', 'acme_fbuild__text', 10, 1 );
		/**
		 * HTML Input TEXT Parser
		 * VERSION 1.0		 *
		 * @param $args
		 *
		 * @return string
		 */
		function acme_fbuild__text ( $args ) {
			$defaults = array (
				'label'    => 'acme_generic_label',
				'name'     => 'acme_generic_field_name',
				'value'    => null,
				'basename' => 'acme_fieldset',
				'class'    => array ( 'acme_generic_form_class' )
			);
			
			$opts = array_merge( $defaults, $args );
			$pattern = is_array( $opts['name'] ) ? '%s[%s]' : '%s-%s';
			$name = is_array( $opts['name'] ) ? reset( $opts['name'] ) : $opts['name'];
			$attrs = null;
			if ( isset( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				$attrs = apply_filters( 'acme_fbuild__custom_attrs', $attrs, $args['custom_attributes'] );
			}
			
			$res       = sprintf( '<legend class="screen-reader-text"><span>%s</span></legend>', $opts['label'] );
			$res       .= sprintf( '<label for="%s"><span>%s:</span></label>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				$opts['label']
			);
			$res       .= sprintf( '<input type="text" id="%1$s" name="%2$s" class="%3$s" value="%4$s" %5$s>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				sprintf( $pattern, $opts['basename'], $name ),
				implode(' ',$opts['class']),
				empty( $opts['value'] ) ? '' : $opts['value'],
				$attrs
			);
			
			return $res;
		}
	}
	if ( !function_exists( 'acme_fbuild__textarea' ) ) {
		add_filter( 'acme_fbuild__textarea', 'acme_fbuild__textarea', 10, 1 );
		/**
		 * HTML Input TEXTAREA Parser
		 * VERSION 1.0		 *
		 * @param $args
		 *
		 * @return string
		 */
		function acme_fbuild__textarea ( $args ) {
			$defaults = array (
				'label'    => 'acme_generic_label',
				'name'     => 'acme_generic_field_name',
				'value'    => null,
				'basename' => 'acme_fieldset',
				'class'    => array ( 'acme_generic_form_class' )
			);
			
			$opts = array_merge( $defaults, $args );
			$pattern = is_array( $opts['name'] ) ? '%s[%s]' : '%s-%s';
			$name = is_array( $opts['name'] ) ? reset( $opts['name'] ) : $opts['name'];
			$attrs = null;
			if ( isset( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				$attrs = apply_filters( 'acme_fbuild__custom_attrs', $attrs, $args['custom_attributes'] );
			}
			
			$res       = sprintf( '<legend class="screen-reader-text"><span>%s</span></legend>', $opts['label'] );
			$res       .= sprintf( '<label for="%s"><span>%s:</span></label>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				$opts['label']
			);
			$res .= sprintf( '<textarea id="%1$s" name="%2$s" class="textarea %3$s" %5$s>%4$s</textarea>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				sprintf( $pattern, $opts['basename'], $name ),
				implode( ' ', $opts['class'] ),
				$opts['value'],
				$attrs
			);
			
			return $res;
		}
	}
	
	if ( !function_exists( 'acme_fbuild__select' ) ) {
		add_filter( 'acme_fbuild__select', 'acme_fbuild__select', 10, 1 );
		/**
		 * HTML Input SELECT BOX Parser
		 * VERSION 1.0		 *
		 * @param $args
		 *
		 * @return string
		 */
		function acme_fbuild__select ( $args ) {
			$defaults = array (
				'label'    => 'acme_generic_label',
				'name'     => 'acme_generic_field_name',
				'value'    => null,
				'options'    => array(),
				'basename' => 'acme_fieldset',
				'class'    => array ( 'acme_generic_form_class' )
			);
			
			$opts = array_merge( $defaults, $args );
			$pattern = is_array( $opts['name'] ) ? '%s[%s]' : '%s-%s';
			$name = is_array( $opts['name'] ) ? reset( $opts['name'] ) : $opts['name'];
			$attrs = null;
			if ( isset( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				$attrs = apply_filters( 'acme_fbuild__custom_attrs', $attrs, $args['custom_attributes'] );
			}
			
			$res       = sprintf( '<legend class="screen-reader-text"><span>%s</span></legend>', $opts['label'] );
			$res       .= sprintf( '<label for="%s"><span>%s:</span></label>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				$opts['label']
			);
			$options = array ();
			foreach (  $opts['options'] as $curValue=>$curLabel ) {
				$options[] = sprintf( '<option value="%s" %s>%s</option>',
					$curValue,
					selected( $opts['value'], $curValue, false ),
					$curLabel
				);
			}
			$res .= sprintf( '<select id="%1$s" name="%2$s" class="textarea %3$s" %5$s>%4$s</select>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				sprintf( $pattern, $opts['basename'], $name ),
				implode( ' ', $opts['class'] ),
				implode( "\n", $options ),
				$attrs
			);
			
			return $res;
		}
	}
	
	if ( !function_exists( 'acme_fbuild__image' ) ) {
		add_filter( 'acme_fbuild__image', 'acme_fbuild__image', 10, 1 );
		/**
		 * HTML Image Uploader Parser
		 * VERSION 1.0
		 * @param $args
		 *
		 * @return string
		 */
		function acme_fbuild__image ( $args ) {
			$defaults = array (
				'label'    => 'acme_generic_label',
				'name'     => 'acme_generic_field_name',
				'value'    => null,
				'basename' => 'acme_fieldset'
			);
			$opts = array_merge( $defaults, $args );
			$pattern = is_array( $opts['name'] ) ? '%s[%s]' : '%s-%s';
			$name = is_array( $opts['name'] ) ? reset( $opts['name'] ) : $opts['name'];
			
			
			$res       = sprintf( '<legend class="screen-reader-text"><span>%s</span></legend>', $opts['label'] );
			$res       .= sprintf( '<label for="%s"><span>%s:</span></label>',
				sprintf( '%s-%s', $opts['basename'], $name ),
				$opts['label']
			);
			if ( $image = wp_get_attachment_image_src( $opts['value'] ) ) {
				$res .= sprintf( '<a href="#" class="%s" data-target="%s"><img src="%s" /></a>',
					sprintf( '%s-img-upl', $opts['basename'] ),
					sprintf( '%s-%s', $opts['basename'], $name ),
					$image[0]
				);
				$res .= sprintf( '<a href="#" class="%s" data-target="%s">%s</a>',
					sprintf( '%s-img-rmv', $opts['basename'] ),
					sprintf( '%s-%s', $opts['basename'], $name ),
					__( 'Remove Image', $opts['basename'] )
				);
				$res .= sprintf( '<input type="hidden" id="%s" name="%s" value="%s">',
					sprintf( '%s-%s', $opts['basename'], $name ),
					sprintf( $pattern, $opts['basename'], $name ),
					empty( $opts['value'] ) ? : $opts['value']
				);
			} else {
				$res .= sprintf( '<a href="#" class="%s" data-target="%s">%s</a>',
					sprintf( '%s-img-upl', $opts['basename'] ),
					sprintf( '%s-%s', $opts['basename'], $name ),
					__( 'Upload Image', $opts['basename'] )
				);
				$res .= sprintf( '<a href="#" class="%s" data-target="%s">%s</a>',
					sprintf( '%s-img-rmv', $opts['basename'] ),
					sprintf( '%s-%s', $opts['basename'], $name ),
					__( 'Remove Image', $opts['basename'] )
				);
				$res .= sprintf( '<input type="hidden" id="%s" name="%s" value="">',
					sprintf( '%s-%s', $opts['basename'], $name ),
					sprintf( $pattern, $opts['basename'], $name )
				);
			}
			
			return $res;
		}
	}
	
	if ( !function_exists( 'acme_fbuild__custom_attrs' ) ) {
		add_filter( 'acme_fbuild__custom_attrs', 'acme_fbuild__custom_attrs', 10, 2 );
		function acme_fbuild__custom_attrs ($html_attr, $args) {
			$arHtml = array ();
			foreach ( $args as $attr=>$val ) {
				$arHtml[] = sprintf( '%s="%s"', $attr, $val );
			}
			$html_attr .= implode( ' ', $arHtml );
			
			return $html_attr;
		}
	}