import { TextareaControl, ToggleControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Field } from '../../ui';
import { CHOICES_KEY, choicesText, parseChoices } from '../ChoiceOptionsPanel';
import type { SpecificationTypeOptionsProps } from '../index';
import LearnedValues from './LearnedValues';
import { valueKey } from './spelling';
import {
	LEARN_KEY,
	SEVERAL_KEY,
	SUGGEST_KEY,
	allowsSeveral,
	learns,
	suggests,
	typedSuggestions,
} from './textOptions';
import './styles.scss';

const TextOptionsPanel = ( {
	options,
	suggestions = [],
	onChange,
	error,
}: SpecificationTypeOptionsProps ) => {
	const [ text, setText ] = useState( () => choicesText( options ) );
	const typed = typedSuggestions( options );
	const typedKeys = new Set( typed.map( valueKey ) );
	const learned = suggestions.filter(
		( suggestion ) =>
			suggestion.learned &&
			! typedKeys.has( valueKey( suggestion.value ) )
	);

	const update = ( changes: Record< string, unknown > ) =>
		onChange( { ...options, ...changes } );

	const promote = ( value: string ) => {
		const next = [ ...typed, value ];

		setText( next.join( '\n' ) );
		update( { [ CHOICES_KEY ]: next } );
	};

	return (
		<>
			<Field label={ __( 'Suggestions', 'product-specifications' ) } wide>
				{ () => (
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Suggest values while typing',
							'product-specifications'
						) }
						help={ __(
							'The most used values come first. Any other value can still be typed.',
							'product-specifications'
						) }
						checked={ suggests( options ) }
						onChange={ ( checked ) =>
							update( { [ SUGGEST_KEY ]: checked } )
						}
					/>
				) }
			</Field>
			{ suggests( options ) && (
				<>
					<Field
						label={ __(
							'Suggested values',
							'product-specifications'
						) }
						help={
							error ??
							__( 'One per line.', 'product-specifications' )
						}
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
									update( {
										[ CHOICES_KEY ]: parseChoices( next ),
									} );
								} }
							/>
						) }
					</Field>
					<Field
						label={ __(
							'Learn new values',
							'product-specifications'
						) }
						wide
					>
						{ () => (
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Also suggest values typed on products',
									'product-specifications'
								) }
								checked={ learns( options ) }
								onChange={ ( checked ) =>
									update( { [ LEARN_KEY ]: checked } )
								}
							/>
						) }
					</Field>
				</>
			) }
			<Field
				label={ __( 'Several values', 'product-specifications' ) }
				wide
			>
				{ () => (
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Allow several values per product',
							'product-specifications'
						) }
						help={ __(
							'Entered as tags and shown on your storefront separated by commas.',
							'product-specifications'
						) }
						checked={ allowsSeveral( options ) }
						onChange={ ( checked ) =>
							update( { [ SEVERAL_KEY ]: checked } )
						}
					/>
				) }
			</Field>
			{ learns( options ) && learned.length > 0 && (
				<LearnedValues learned={ learned } onPromote={ promote } />
			) }
		</>
	);
};

export default TextOptionsPanel;
