import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { DefaultField } from '../defaultControls';
import { asText, fromText } from '../textConversion';
import type {
	SpecificationTypeDefaultProps,
	SpecificationTypeValueProps,
	Suggestion,
} from '../index';
import SeveralValues from './SeveralValues';
import SuggestedText from './SuggestedText';
import { allowsSeveral, suggests } from './textOptions';

interface ControlProps {
	label: string;
	value: unknown;
	options: Record< string, unknown >;
	suggestions: Suggestion[];
	onChange: ( value: unknown ) => void;
	inputProps: Record< string, unknown >;
}

const TextInput = ( {
	label,
	value,
	options,
	suggestions,
	onChange,
	inputProps,
}: ControlProps ) => {
	const offered = suggests( options ) ? suggestions : [];

	if ( allowsSeveral( options ) ) {
		return (
			<SeveralValues
				label={ label }
				hideLabel
				value={ value }
				suggestions={ offered }
				onChange={ onChange }
			/>
		);
	}

	if ( suggests( options ) ) {
		return (
			<SuggestedText
				value={ value }
				suggestions={ offered }
				onChange={ onChange }
				inputProps={ inputProps }
			/>
		);
	}

	return (
		<TextControl
			{ ...inputProps }
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			value={ asText( value ) }
			onChange={ ( next ) => onChange( fromText( next ) ) }
		/>
	);
};

export const TextValue = ( {
	label,
	value,
	options,
	suggestions = [],
	onChange,
}: SpecificationTypeValueProps ) => (
	<TextInput
		label={ label }
		value={ value }
		options={ options }
		suggestions={ suggestions }
		onChange={ onChange }
		inputProps={ { label, hideLabelFromVision: true } }
	/>
);

export const TextDefault = ( {
	value,
	options,
	suggestions = [],
	onChange,
}: SpecificationTypeDefaultProps ) => (
	<DefaultField>
		{ ( controlProps ) => (
			<TextInput
				label={ __( 'Default', 'product-specifications' ) }
				value={ value }
				options={ options }
				suggestions={ suggestions }
				onChange={ onChange }
				inputProps={ {
					...controlProps,
					placeholder: __( 'None', 'product-specifications' ),
				} }
			/>
		) }
	</DefaultField>
);
