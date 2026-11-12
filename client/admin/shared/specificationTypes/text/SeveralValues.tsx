import { FormTokenField } from '@wordpress/components';
import clsx from 'clsx';
import { isTextList } from '../textConversion';
import type { Suggestion } from '../index';
import { tokensFrom } from './spelling';
import './styles.scss';

const MAX_SHOWN = 8;

interface Props {
	label: string;
	hideLabel?: boolean;
	value: unknown;
	suggestions: Suggestion[];
	onChange: ( value: string[] | null ) => void;
}

const asTokens = ( value: unknown ): string[] => {
	if ( isTextList( value ) ) {
		return value;
	}

	return typeof value === 'string' && value !== '' ? [ value ] : [];
};

// The suggestions arrive ranked and the field keeps their order within its own
// prefix-first matching, so the most used values stay on top.
const SeveralValues = ( {
	label,
	hideLabel,
	value,
	suggestions,
	onChange,
}: Props ) => {
	const known = suggestions.map( ( suggestion ) => suggestion.value );

	return (
		<div
			className={ clsx( 'ps-several-values', {
				'is-label-hidden': hideLabel,
			} ) }
		>
			<FormTokenField
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ label }
				value={ asTokens( value ) }
				suggestions={ known }
				maxSuggestions={ MAX_SHOWN }
				__experimentalExpandOnFocus={ known.length > 0 }
				tokenizeOnBlur
				onChange={ ( tokens ) => {
					const next = tokensFrom(
						tokens.map( ( token ) =>
							typeof token === 'string' ? token : token.value
						),
						known
					);

					onChange( next.length > 0 ? next : null );
				} }
			/>
		</div>
	);
};

export default SeveralValues;
