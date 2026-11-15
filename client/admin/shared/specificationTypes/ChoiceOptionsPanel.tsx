import { TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Field } from '../ui';
import type { SpecificationTypeOptionsProps } from './index';

export const CHOICES_KEY = 'values';

export function parseChoices( text: string ): string[] {
	return text
		.split( '\n' )
		.map( ( choice ) => choice.trim() )
		.filter( ( choice ) => choice !== '' );
}

export function choicesFrom( options: Record< string, unknown > ): string[] {
	const stored = options[ CHOICES_KEY ];

	return Array.isArray( stored )
		? stored.map( ( choice ) => String( choice ) )
		: [];
}

export function choicesText( options: Record< string, unknown > ): string {
	return choicesFrom( options ).join( '\n' );
}

// Editing the choices does not rewrite values already stored against them, so
// a current value no longer among the choices is listed rather than silently
// swapped for one of the remaining choices.
export function choiceSelectOptions(
	choices: string[],
	current: string,
	emptyLabel: string
): Array< { value: string; label: string } > {
	const orphaned = current !== '' && ! choices.includes( current );

	return [
		{ value: '', label: emptyLabel },
		...( orphaned
			? [
					{
						value: current,
						label: sprintf(
							/* translators: %s: a stored value no longer present in the choices. */
							__(
								'%s — no longer an option',
								'product-specifications'
							),
							current
						),
					},
			  ]
			: [] ),
		...choices.map( ( choice ) => ( { value: choice, label: choice } ) ),
	];
}

// One choice per line. A list of inputs with add and remove buttons is more
// controls to tab through, and entering a hundred specifications without the
// mouse is what this form is for.
const ChoiceOptionsPanel = ( {
	options,
	onChange,
	error,
}: SpecificationTypeOptionsProps ) => {
	// The textarea holds the text, not the parsed choices. Deriving its value
	// back through the parser would strip the newline that starts a choice as
	// soon as it is typed, leaving the second one unreachable.
	const [ text, setText ] = useState( () => choicesText( options ) );

	return (
		<Field
			label={ __( 'Options', 'product-specifications' ) }
			help={ error ?? __( 'One per line.', 'product-specifications' ) }
			helpTone={ error ? 'error' : 'neutral' }
			wide
		>
			{ ( controlProps ) => (
				<TextareaControl
					{ ...controlProps }
					__nextHasNoMarginBottom
					value={ text }
					onChange={ ( next ) => {
						setText( next );
						onChange( {
							...options,
							[ CHOICES_KEY ]: parseChoices( next ),
						} );
					} }
				/>
			) }
		</Field>
	);
};

export default ChoiceOptionsPanel;
