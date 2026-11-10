import { SelectControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { choiceSelectOptions, choicesFrom } from './ChoiceOptionsPanel';
import { asText, fromText } from './textConversion';
import type { SpecificationTypeValueProps } from './index';

export const ParagraphValue = ( {
	label,
	value,
	onChange,
}: SpecificationTypeValueProps ) => (
	<TextareaControl
		__nextHasNoMarginBottom
		label={ label }
		hideLabelFromVision
		value={ asText( value ) }
		onChange={ ( next ) => onChange( fromText( next ) ) }
	/>
);

export const ChoiceValue = ( {
	label,
	value,
	options,
	onChange,
}: SpecificationTypeValueProps ) => (
	<SelectControl
		__nextHasNoMarginBottom
		__next40pxDefaultSize
		label={ label }
		hideLabelFromVision
		value={ asText( value ) }
		options={ choiceSelectOptions(
			choicesFrom( options ),
			asText( value ),
			__( 'No value', 'product-specifications' )
		) }
		onChange={ ( next ) => onChange( fromText( next ) ) }
	/>
);

export type TriState = '' | 'yes' | 'no';

export const toTriState = ( value: unknown ): TriState => {
	if ( value === true ) {
		return 'yes';
	}
	if ( value === false ) {
		return 'no';
	}
	return '';
};

export const fromTriState = ( next: string ): boolean | null => {
	if ( next === 'yes' ) {
		return true;
	}
	if ( next === 'no' ) {
		return false;
	}
	return null;
};

export const TrueFalseValue = ( {
	label,
	value,
	onChange,
}: SpecificationTypeValueProps ) => (
	<SelectControl
		__nextHasNoMarginBottom
		__next40pxDefaultSize
		label={ label }
		hideLabelFromVision
		value={ toTriState( value ) }
		options={ [
			{ value: '', label: __( 'No value', 'product-specifications' ) },
			{ value: 'yes', label: __( 'Yes', 'product-specifications' ) },
			{ value: 'no', label: __( 'No', 'product-specifications' ) },
		] }
		onChange={ ( next ) => onChange( fromTriState( next ) ) }
	/>
);
