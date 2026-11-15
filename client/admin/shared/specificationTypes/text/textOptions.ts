import { __ } from '@wordpress/i18n';
import { choicesFrom } from '../ChoiceOptionsPanel';

export const SUGGEST_KEY = 'suggest';
export const SEVERAL_KEY = 'multiple';
export const LEARN_KEY = 'learn';

type Options = Record< string, unknown >;

export const suggests = ( options: Options ) => options[ SUGGEST_KEY ] === true;

export const allowsSeveral = ( options: Options ) =>
	options[ SEVERAL_KEY ] === true;

export const learns = ( options: Options ) =>
	suggests( options ) && options[ LEARN_KEY ] === true;

export const typedSuggestions = choicesFrom;

export const requiresSuggestions = ( options: Options ) =>
	suggests( options ) &&
	! learns( options ) &&
	typedSuggestions( options ).length === 0
		? __(
				'Add at least one suggestion, or let new values be learned.',
				'product-specifications'
		  )
		: undefined;
