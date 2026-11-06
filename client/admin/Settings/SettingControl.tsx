import {
	Button,
	CheckboxControl,
	RadioControl,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import type {
	SettingFieldDefinition,
	SettingOption,
	SettingValue,
} from './types';

interface Props {
	field: SettingFieldDefinition;
	value: SettingValue;
	error?: string;
	readOnly: boolean;
	onChange: ( value: SettingValue ) => void;
}

const optionLabel = ( options: SettingOption[], value: string ): string =>
	options.find( ( option ) => option.value === value )?.label ?? value;

const valueLabel = (
	field: SettingFieldDefinition,
	value: SettingValue
): string => {
	if ( typeof value === 'boolean' ) {
		return value
			? __( 'On', 'product-specifications' )
			: __( 'Off', 'product-specifications' );
	}

	if ( Array.isArray( value ) ) {
		return value
			.map( ( item ) => optionLabel( field.options, item ) )
			.join( ', ' );
	}

	return field.options.length > 0
		? optionLabel( field.options, String( value ) )
		: String( value );
};

const isDefaultValue = (
	value: SettingValue,
	defaultValue: SettingValue
): boolean => JSON.stringify( value ) === JSON.stringify( defaultValue );

const EditableControl = ( {
	field,
	value,
	onChange,
}: Omit< Props, 'error' | 'readOnly' > ) => {
	const hiddenLabel = field.controlLabel || field.label;

	switch ( field.type ) {
		case 'text':
			return (
				<TextControl
					label={ hiddenLabel }
					hideLabelFromVision
					value={ String( value ) }
					onChange={ onChange }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);
		case 'textarea':
			return (
				<TextareaControl
					label={ hiddenLabel }
					hideLabelFromVision
					value={ String( value ) }
					onChange={ onChange }
					__nextHasNoMarginBottom
				/>
			);
		case 'number':
		case 'media':
			return (
				<TextControl
					label={ hiddenLabel }
					hideLabelFromVision
					type="number"
					min={ field.type === 'media' ? 0 : undefined }
					value={ String( value ) }
					onChange={ ( next ) => onChange( Number( next ) ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);
		case 'toggle':
			return (
				<ToggleControl
					label={ field.controlLabel || field.label }
					checked={ Boolean( value ) }
					onChange={ onChange }
					__nextHasNoMarginBottom
				/>
			);
		case 'select': {
			const stringValue = String( value );
			const hasValue = field.options.some(
				( option ) => option.value === stringValue
			);
			const options = hasValue
				? field.options
				: [
						{
							value: stringValue,
							label: sprintf(
								/* translators: %s: invalid stored setting value. */
								__(
									'%s — no longer available',
									'product-specifications'
								),
								stringValue
							),
						},
						...field.options,
				  ];

			return (
				<SelectControl
					label={ hiddenLabel }
					hideLabelFromVision
					value={ stringValue }
					options={ options }
					onChange={ onChange }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			);
		}
		case 'radio':
			return (
				<RadioControl
					label={ hiddenLabel }
					hideLabelFromVision
					selected={ String( value ) }
					options={ field.options }
					onChange={ onChange }
				/>
			);
		case 'checkbox_group': {
			const selected = Array.isArray( value ) ? value : [];
			return (
				<div
					className="ps-setting__checkboxes"
					role="group"
					aria-label={ hiddenLabel }
				>
					{ field.options.map( ( option ) => (
						<CheckboxControl
							key={ option.value }
							label={ option.label }
							checked={ selected.includes( option.value ) }
							onChange={ ( checked ) =>
								onChange(
									checked
										? [ ...selected, option.value ]
										: selected.filter(
												( item ) =>
													item !== option.value
										  )
								)
							}
							__nextHasNoMarginBottom
						/>
					) ) }
				</div>
			);
		}
		case 'color':
			return (
				<input
					className="ps-setting__color"
					type="color"
					aria-label={ hiddenLabel }
					value={ String( value ) }
					onChange={ ( event ) => onChange( event.target.value ) }
				/>
			);
	}
};

const SettingControl = ( {
	field,
	value,
	error,
	readOnly,
	onChange,
}: Props ) => {
	const atDefault = isDefaultValue( value, field.default );

	return (
		<div className="ps-setting">
			<div className="ps-setting__label">
				<strong>{ field.label }</strong>
				<p>{ field.description }</p>
			</div>
			<div className="ps-setting__control">
				{ readOnly ? (
					<span className="ps-setting__readonly">
						{ valueLabel( field, value ) }
					</span>
				) : (
					<EditableControl
						field={ field }
						value={ value }
						onChange={ onChange }
					/>
				) }
				{ error && (
					<p className="ps-setting__error" role="alert">
						{ error }{ ' ' }
						{ __(
							'Choose a value to replace it.',
							'product-specifications'
						) }
					</p>
				) }
				{ ! error && ! readOnly && (
					<div className="ps-setting__default">
						<span>
							{ atDefault
								? sprintf(
										/* translators: %s: default setting value. */
										__(
											'Default: %s',
											'product-specifications'
										),
										valueLabel( field, field.default )
								  )
								: sprintf(
										/* translators: %s: default setting value. */
										__(
											'Changed from the default (%s)',
											'product-specifications'
										),
										valueLabel( field, field.default )
								  ) }
						</span>
						{ ! atDefault && (
							<Button
								variant="tertiary"
								size="compact"
								onClick={ () => onChange( field.default ) }
							>
								{ __(
									'Reset to default',
									'product-specifications'
								) }
							</Button>
						) }
					</div>
				) }
			</div>
		</div>
	);
};

export default SettingControl;
