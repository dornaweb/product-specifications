import { TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Field } from '../../ui';
import { parseNumber } from './numberInput';

interface Props {
	label: string;
	value: number | null;
	onChange: ( value: number | null ) => void;
}

const BoundField = ( { label, value, onChange }: Props ) => {
	const [ text, setText ] = useState( value === null ? '' : String( value ) );

	return (
		<Field
			label={ label }
			help={ __(
				'Optional. Applies to the number, whatever unit is chosen.',
				'product-specifications'
			) }
		>
			{ ( controlProps ) => (
				<TextControl
					{ ...controlProps }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					inputMode="decimal"
					value={ text }
					onChange={ ( next ) => {
						setText( next );
						onChange( parseNumber( next ) );
					} }
				/>
			) }
		</Field>
	);
};

export default BoundField;
