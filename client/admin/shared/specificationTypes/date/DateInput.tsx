import { SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fromText } from '../textConversion';
import { datePrecision } from './dateOptions';
import { dateParts, fullDate, monthValue } from './dateParts';
import './styles.scss';

interface Props {
	label: string;
	value: unknown;
	options: Record< string, unknown >;
	onChange: ( value: unknown ) => void;
	fieldProps?: Record< string, unknown >;
}

function monthChoices() {
	const names = new Intl.DateTimeFormat(
		document.documentElement.lang || undefined,
		{ month: 'long', timeZone: 'UTC' }
	);

	return Array.from( { length: 12 }, ( _, index ) => ( {
		value: String( index + 1 ).padStart( 2, '0' ),
		label: names.format( Date.UTC( 2000, index, 1 ) ),
	} ) );
}

const DateInput = ( {
	label,
	value,
	options,
	onChange,
	fieldProps,
}: Props ) => {
	const precision = datePrecision( options );
	const parts = dateParts( value );

	if ( precision === 'day' ) {
		return (
			<TextControl
				{ ...fieldProps }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ label }
				hideLabelFromVision
				type="date"
				value={ fullDate( value ) }
				onChange={ ( next ) => onChange( fromText( next ) ) }
			/>
		);
	}

	const yearField = ( props?: Record< string, unknown > ) => (
		<TextControl
			{ ...props }
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={
				precision === 'year'
					? label
					: __( 'Year', 'product-specifications' )
			}
			hideLabelFromVision
			inputMode="numeric"
			maxLength={ 4 }
			placeholder={ __( 'Year', 'product-specifications' ) }
			value={ parts.year }
			onChange={ ( year ) =>
				onChange(
					precision === 'year'
						? fromText( year )
						: monthValue( { ...parts, year } )
				)
			}
		/>
	);

	if ( precision === 'year' ) {
		return <div className="ps-date-input">{ yearField( fieldProps ) }</div>;
	}

	return (
		<div className="ps-date-input">
			<SelectControl
				{ ...fieldProps }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ label }
				hideLabelFromVision
				value={ parts.month }
				options={ [
					{
						value: '',
						label: __( 'Month', 'product-specifications' ),
					},
					...monthChoices(),
				] }
				onChange={ ( month ) =>
					onChange( monthValue( { ...parts, month } ) )
				}
			/>
			{ yearField() }
		</div>
	);
};

export default DateInput;
