export interface EnteredLink {
	url: string;
	label: string;
}

export interface StoredLink {
	url: string;
	label?: string;
}

const text = ( value: unknown ) => ( typeof value === 'string' ? value : '' );

// A link can be stored as a bare address, which reads as one without a label.
export function enteredLink( value: unknown ): EnteredLink {
	if ( typeof value === 'string' ) {
		return { url: value, label: '' };
	}

	const stored = (
		value !== null && typeof value === 'object' ? value : {}
	) as Record< string, unknown >;

	return { url: text( stored.url ), label: text( stored.label ) };
}

export function storedLink( entered: EnteredLink ): StoredLink | null {
	if ( entered.url === '' && entered.label === '' ) {
		return null;
	}

	return entered.label === ''
		? { url: entered.url }
		: { url: entered.url, label: entered.label };
}
