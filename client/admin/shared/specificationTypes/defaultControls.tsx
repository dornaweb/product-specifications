import {
	CheckboxControl,
	SelectControl,
	TextareaControl,
} from '@wordpress/components';
import type { ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import { Field } from '../ui';
import { choiceSelectOptions, choicesFrom } from './ChoiceOptionsPanel';
import { asText, fromText } from './textConversion';
import DateInput from './date/DateInput';
import type { SpecificationTypeDefaultProps } from './index';
import NumberInput from './number/NumberInput';

export const DefaultField = ( {
	children,
}: {
	children: ( controlProps: Record< string, unknown > ) => ReactNode;
} ) => (
	<Field
		label={ __( 'Default', 'product-specifications' ) }
		help={ __(
			'Offered as a suggestion when a product has no value yet. Never stored until the product is saved, and never shown on your storefront on its own.',
			'product-specifications'
		) }
	>
		{ children }
	</Field>
);

export const ParagraphDefault = ( {
	value,
	onChange,
}: SpecificationTypeDefaultProps ) => (
	<DefaultField>
		{ ( controlProps ) => (
			<TextareaControl
				{ ...controlProps }
				__nextHasNoMarginBottom
				value={ asText( value ) }
				placeholder={ __( 'None', 'product-specifications' ) }
				onChange={ ( text ) => onChange( fromText( text ) ) }
			/>
		) }
	</DefaultField>
);

export const ChoiceDefault = ( {
	value,
	options,
	onChange,
}: SpecificationTypeDefaultProps ) => (
	<DefaultField>
		{ ( controlProps ) => (
			<SelectControl
				{ ...controlProps }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				value={ asText( value ) }
				options={ choiceSelectOptions(
					choicesFrom( options ),
					asText( value ),
					__( 'None', 'product-specifications' )
				) }
				onChange={ ( choice ) => onChange( fromText( choice ) ) }
			/>
		) }
	</DefaultField>
);

export const TrueFalseDefault = ( {
	value,
	onChange,
}: SpecificationTypeDefaultProps ) => (
	<DefaultField>
		{ ( controlProps ) => (
			<CheckboxControl
				{ ...controlProps }
				__nextHasNoMarginBottom
				label={ __( 'Yes', 'product-specifications' ) }
				checked={ value === true }
				onChange={ ( checked ) => onChange( checked ? true : null ) }
			/>
		) }
	</DefaultField>
);

export const NumberDefault = ( {
	value,
	options,
	onChange,
}: SpecificationTypeDefaultProps ) => (
	<DefaultField>
		{ ( controlProps ) => (
			<NumberInput
				label={ __( 'Default', 'product-specifications' ) }
				value={ value }
				options={ options }
				onChange={ onChange }
				fieldProps={ controlProps }
			/>
		) }
	</DefaultField>
);

export const DateDefault = ( {
	value,
	options,
	onChange,
}: SpecificationTypeDefaultProps ) => (
	<DefaultField>
		{ ( controlProps ) => (
			<DateInput
				label={ __( 'Default', 'product-specifications' ) }
				value={ value }
				options={ options }
				onChange={ onChange }
				fieldProps={ controlProps }
			/>
		) }
	</DefaultField>
);
