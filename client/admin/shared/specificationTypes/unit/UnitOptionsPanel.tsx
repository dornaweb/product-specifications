import { SelectControl, ToggleControl } from '@wordpress/components';
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
	UNITS,
	unitOptions,
} from './unitOptions';
import './styles.scss';

const DECIMAL_CHOICES = [ 0, 1, 2, 3, 4 ];

const UnitOptionsPanel = ( {
	options,
	onChange,
	error,
}: SpecificationTypeOptionsProps ) => {
	const current = unitOptions( options );
	const kinds = unitKinds();
	const kind = kinds.find( ( item ) => item.id === current.kind );

	const update = ( changes: Record< string, unknown > ) => {
		const next = { ...options, ...changes };

		onChange( {
			...next,
			[ DEFAULT_UNIT ]: unitOptions( next ).defaultUnit,
		} );
	};

	return (
		<>
			<Field
				label={ __( 'Measures', 'product-specifications' ) }
				help={ current.kind === '' ? error : undefined }
				helpTone="error"
			>
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
									'Choose what it measures',
									'product-specifications'
								),
								disabled: true,
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
			<Field label={ __( 'Decimal places', 'product-specifications' ) }>
				{ ( controlProps ) => (
					<SelectControl
						{ ...controlProps }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						value={ String( current.decimals ) }
						options={ DECIMAL_CHOICES.map( ( places ) => ( {
							value: String( places ),
							label:
								places === 0
									? __(
											'None, whole numbers',
											'product-specifications'
									  )
									: String( places ),
						} ) ) }
						onChange={ ( places ) =>
							update( { [ DECIMALS ]: Number( places ) } )
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
			<BoundField
				label={ __( 'Minimum', 'product-specifications' ) }
				value={ current.min }
				onChange={ ( min ) => update( { [ MIN ]: min } ) }
			/>
			<BoundField
				label={ __( 'Maximum', 'product-specifications' ) }
				value={ current.max }
				onChange={ ( max ) => update( { [ MAX ]: max } ) }
			/>
		</>
	);
};

export default UnitOptionsPanel;
