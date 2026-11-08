import { CheckboxControl, TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Field } from '../../ui';
import { parseChoices } from '../ChoiceOptionsPanel';
import type { Unit, UnitKind } from './catalog';

interface Props {
	kind: UnitKind | undefined;
	chosen: Unit[];
	error?: string;
	onChange: ( units: Unit[] ) => void;
}

const isChosen = ( chosen: Unit[], unit: Unit ) =>
	chosen.some( ( item ) => item.id === unit.id );

const UnitsField = ( { kind, chosen, error, onChange }: Props ) => {
	// A custom list is kept as typed: parsing it back on every keystroke would drop
	// the newline that starts the next unit.
	const [ text, setText ] = useState( () =>
		chosen.map( ( unit ) => unit.symbol ).join( '\n' )
	);

	const help =
		error ??
		( kind
			? __(
					'The units a product can be given. Values are shown as entered, never converted.',
					'product-specifications'
			  )
			: __(
					'One per line, as it should appear after the number.',
					'product-specifications'
			  ) );

	return (
		<Field
			label={ __( 'Units', 'product-specifications' ) }
			help={ help }
			helpTone={ error ? 'error' : 'neutral' }
			wide
		>
			{ ( controlProps ) =>
				kind ? (
					<div className="ps-unit-choices">
						{ kind.units.map( ( unit ) => (
							<CheckboxControl
								key={ unit.id }
								__nextHasNoMarginBottom
								label={ unit.symbol }
								checked={ isChosen( chosen, unit ) }
								onChange={ ( checked ) =>
									onChange(
										kind.units.filter( ( candidate ) =>
											candidate.id === unit.id
												? checked
												: isChosen( chosen, candidate )
										)
									)
								}
							/>
						) ) }
					</div>
				) : (
					<TextareaControl
						{ ...controlProps }
						__nextHasNoMarginBottom
						value={ text }
						onChange={ ( next ) => {
							setText( next );
							onChange(
								parseChoices( next ).map( ( symbol ) => ( {
									id: symbol,
									symbol,
								} ) )
							);
						} }
					/>
				)
			}
		</Field>
	);
};

export default UnitsField;
