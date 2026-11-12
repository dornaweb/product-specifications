import { TextControl } from '@wordpress/components';
import { useId } from '@wordpress/element';
import { asText, fromText } from '../textConversion';
import type { Suggestion } from '../index';
import { knownSpelling } from './spelling';

interface Props {
	value: unknown;
	suggestions: Suggestion[];
	onChange: ( value: string | null ) => void;
	inputProps: Record< string, unknown >;
}

// A plain text field keeps commas typeable. The browser's own list offers the
// suggestions, and leaving the field gives a variant the known spelling.
const SuggestedText = ( {
	value,
	suggestions,
	onChange,
	inputProps,
}: Props ) => {
	const listId = useId();
	const known = suggestions.map( ( suggestion ) => suggestion.value );
	const text = asText( value );

	return (
		<>
			<TextControl
				{ ...inputProps }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				list={ listId }
				autoComplete="off"
				value={ text }
				onChange={ ( next ) => onChange( fromText( next ) ) }
				onBlur={ () => {
					const spelled = knownSpelling( text, known );

					if ( spelled !== text ) {
						onChange( fromText( spelled ) );
					}
				} }
			/>
			<datalist id={ listId }>
				{ known.map( ( suggestion ) => (
					<option key={ suggestion } value={ suggestion } />
				) ) }
			</datalist>
		</>
	);
};

export default SuggestedText;
