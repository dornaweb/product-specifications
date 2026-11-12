import {
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Field } from '../../ui';
import type { SpecificationTypeOptionsProps } from '../index';
import BoundField from './BoundField';
import { CUSTOM_KIND, unitKinds } from './catalog';
import UnitsField from './UnitsField';
import {
	DECIMALS,
	DEFAULT_UNIT,
	KIND,
	MAX,
	MIN,
	RANGE,
	THOUSANDS,
	UNITS,
	numberOptions,
} from './numberOptions';
import './styles.scss';

const MAX_DECIMALS = 4;

const NumberOptionsPanel = ( {
	options,
	onChange,
	error,
}: SpecificationTypeOptionsProps ) => {
	const current = numberOptions( options );
	const kinds = unitKinds();
	const kind = kinds.find( ( item ) => item.id === current.kind );

	const update = ( changes: Record< string, unknown > ) => {
		const next = { ...options, ...changes };

		onChange( {
			...next,
			[ DEFAULT_UNIT ]: numberOptions( next ).defaultUnit,
		} );
	};

	return (
		<>
			<Field label={ __( 'Unit', 'product-specifications' ) }>
				{ ( controlProps ) => (
					<SelectControl
						{ ...controlProps }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						value={ current.kind }
						options={ [
							{
								value: '',
								label: __(
									'No unit, a plain number',
									'product-specifications'
								),
							},
							...kinds.map( ( item ) => ( {
								value: item.id,
								label: item.label,
							} ) ),
							{
								value: CUSTOM_KIND,
								label: __(
									'Something else',
									'product-specifications'
								),
							},
						] }
						onChange={ ( id ) =>
							update( {
								[ KIND ]: id,
								[ UNITS ]:
									kinds.find( ( item ) => item.id === id )
										?.units ?? [],
							} )
						}
					/>
				) }
			</Field>
			{ current.kind !== '' && (
				<UnitsField
					key={ current.kind }
					kind={ kind }
					chosen={ current.units }
					error={ error }
					onChange={ ( units ) => update( { [ UNITS ]: units } ) }
				/>
			) }
			{ current.units.length > 1 && (
				<Field label={ __( 'Default unit', 'product-specifications' ) }>
					{ ( controlProps ) => (
						<SelectControl
							{ ...controlProps }
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							value={ current.defaultUnit }
							options={ current.units.map( ( unit ) => ( {
								value: unit.id,
								label: unit.symbol,
							} ) ) }
							onChange={ ( unit ) =>
								update( { [ DEFAULT_UNIT ]: unit } )
							}
						/>
					) }
				</Field>
			) }
			<Field
				label={ __( 'Decimal places', 'product-specifications' ) }
				help={ __(
					'0 for whole numbers, up to 4.',
					'product-specifications'
				) }
			>
				{ ( controlProps ) => (
					<TextControl
						{ ...controlProps }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						type="number"
						min={ 0 }
						max={ MAX_DECIMALS }
						step={ 1 }
						value={ String( current.decimals ) }
						onChange={ ( places ) =>
							update( {
								[ DECIMALS ]: Math.min(
									MAX_DECIMALS,
									Math.max(
										0,
										Math.trunc( Number( places ) ) || 0
									)
								),
							} )
						}
					/>
				) }
			</Field>
			<Field label={ __( 'Range', 'product-specifications' ) } wide>
				{ () => (
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Allow a range, such as 100–240 V',
							'product-specifications'
						) }
						checked={ current.range }
						onChange={ ( range ) => update( { [ RANGE ]: range } ) }
					/>
				) }
			</Field>
			<Field
				label={ __( 'Thousands', 'product-specifications' ) }
				help={ __(
					'Turn off for years and codes, such as 2026.',
					'product-specifications'
				) }
				wide
			>
				{ () => (
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Separate thousands',
							'product-specifications'
						) }
						checked={ current.thousands }
						onChange={ ( thousands ) =>
							update( { [ THOUSANDS ]: thousands } )
						}
					/>
				) }
			</Field>
			<BoundField
				label={ __( 'Minimum', 'product-specifications' ) }
				hasUnits={ current.units.length > 0 }
				value={ current.min }
				onChange={ ( min ) => update( { [ MIN ]: min } ) }
			/>
			<BoundField
				label={ __( 'Maximum', 'product-specifications' ) }
				hasUnits={ current.units.length > 0 }
				value={ current.max }
				onChange={ ( max ) => update( { [ MAX ]: max } ) }
			/>
		</>
	);
};

export default NumberOptionsPanel;
