import { SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { numberOptions } from './numberOptions';
import './styles.scss';

interface Props {
	label: string;
	value: unknown;
	options: Record< string, unknown >;
	onChange: ( value: unknown ) => void;
	fieldProps?: Record< string, unknown >;
}

interface Entered {
	from: string;
	to: string;
	unit: string;
}

const text = ( value: unknown ) =>
	typeof value === 'number' || typeof value === 'string'
		? String( value )
		: '';

function entered( value: unknown, defaultUnit: string ): Entered {
	const stored = (
		value !== null && typeof value === 'object' ? value : {}
	) as Record< string, unknown >;

	return {
		from: text( 'value' in stored ? stored.value : stored.min ),
		to: text( stored.max ),
		unit: typeof stored.unit === 'string' ? stored.unit : defaultUnit,
	};
}

const NumberInput = ( {
	label,
	value,
	options,
	onChange,
	fieldProps,
}: Props ) => {
	const settings = numberOptions( options );
	const current = entered( value, settings.defaultUnit );
	const hasUnits = settings.units.length > 0;
	const unitAllowed = settings.units.some(
		( unit ) => unit.id === current.unit
	);

	const emit = ( next: Entered ) => {
		if ( next.from === '' && next.to === '' ) {
			onChange( null );
			return;
		}

		const numbers = settings.range
			? { min: next.from, max: next.to }
			: { value: next.from };

		onChange( hasUnits ? { ...numbers, unit: next.unit } : numbers );
	};

	const numberField = (
		part: 'from' | 'to',
		fieldLabel: string,
		props?: Record< string, unknown >
	) => (
		<TextControl
			{ ...props }
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ fieldLabel }
			hideLabelFromVision
			inputMode="decimal"
			value={ current[ part ] }
			onChange={ ( next ) => emit( { ...current, [ part ]: next } ) }
		/>
	);

	return (
		<div className="ps-number-input">
			{ numberField( 'from', label, fieldProps ) }
			{ settings.range && (
				<>
					<span aria-hidden="true">–</span>
					{ numberField(
						'to',
						__( 'Up to (optional)', 'product-specifications' )
					) }
				</>
			) }
			{ hasUnits && ( settings.units.length > 1 || ! unitAllowed ) && (
				<SelectControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Unit', 'product-specifications' ) }
					hideLabelFromVision
					value={ current.unit }
					options={ [
						...( unitAllowed
							? []
							: [
									{
										value: current.unit,
										label: current.unit,
									},
							  ] ),
						...settings.units.map( ( unit ) => ( {
							value: unit.id,
							label: unit.symbol,
						} ) ),
					] }
					onChange={ ( unit ) => emit( { ...current, unit } ) }
				/>
			) }
			{ hasUnits && settings.units.length === 1 && unitAllowed && (
				<span className="ps-number-input__unit">
					{ settings.units[ 0 ].symbol }
				</span>
			) }
		</div>
	);
};

export default NumberInput;
