import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Field } from '../../ui';
import type { SpecificationTypeOptionsProps } from '../index';
import { PRECISION, datePrecision } from './dateOptions';

const DateOptionsPanel = ( {
	options,
	onChange,
}: SpecificationTypeOptionsProps ) => (
	<Field
		label={ __( 'Precision', 'product-specifications' ) }
		help={ __(
			'Choose a month or a year when the day is not known, such as a release in March 2026.',
			'product-specifications'
		) }
	>
		{ ( controlProps ) => (
			<SelectControl
				{ ...controlProps }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				value={ datePrecision( options ) }
				options={ [
					{
						value: 'day',
						label: __( 'Day', 'product-specifications' ),
					},
					{
						value: 'month',
						label: __( 'Month', 'product-specifications' ),
					},
					{
						value: 'year',
						label: __( 'Year', 'product-specifications' ),
					},
				] }
				onChange={ ( precision ) =>
					onChange( { ...options, [ PRECISION ]: precision } )
				}
			/>
		) }
	</Field>
);

export default DateOptionsPanel;
