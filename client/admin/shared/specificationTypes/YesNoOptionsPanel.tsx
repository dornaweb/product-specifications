import { SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Field } from '../ui';
import type { SpecificationTypeOptionsProps } from './index';

export const DISPLAY_KEY = 'display';
export const LABELS_KEY = 'labels';

const YesNoOptionsPanel = ( {
	options,
	onChange,
}: SpecificationTypeOptionsProps ) => {
	const display = options[ DISPLAY_KEY ] === 'text' ? 'text' : 'icons';
	const labels =
		typeof options[ LABELS_KEY ] === 'string' ? options[ LABELS_KEY ] : '';

	return (
		<>
			<Field
				label={ __( 'Show on the store as', 'product-specifications' ) }
			>
				{ ( controlProps ) => (
					<SelectControl
						{ ...controlProps }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						value={ display }
						options={ [
							{
								value: 'icons',
								label: __(
									'Check and cross icons',
									'product-specifications'
								),
							},
							{
								value: 'text',
								label: __( 'Text', 'product-specifications' ),
							},
						] }
						onChange={ ( next ) =>
							onChange( { ...options, [ DISPLAY_KEY ]: next } )
						}
					/>
				) }
			</Field>
			{ display === 'text' && (
				<Field
					label={ __( 'Wording', 'product-specifications' ) }
					help={ __(
						'The words for yes and no, separated by |. Leave empty to use the translated Yes and No.',
						'product-specifications'
					) }
				>
					{ ( controlProps ) => (
						<TextControl
							{ ...controlProps }
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							value={ labels }
							placeholder={ __(
								'Yes|No',
								'product-specifications'
							) }
							onChange={ ( next ) =>
								onChange( { ...options, [ LABELS_KEY ]: next } )
							}
						/>
					) }
				</Field>
			) }
		</>
	);
};

export default YesNoOptionsPanel;
