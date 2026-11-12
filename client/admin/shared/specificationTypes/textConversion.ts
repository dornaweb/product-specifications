export const isTextList = ( value: unknown ): value is string[] =>
	Array.isArray( value ) &&
	value.every( ( item ) => typeof item === 'string' );

// A list stored while the specification takes one value reads as its items joined,
// which is how the storefront shows it.
export const asText = ( value: unknown ) => {
	if ( typeof value === 'string' ) {
		return value;
	}

	return isTextList( value ) ? value.join( ', ' ) : '';
};

// An empty control is the absence of a value, not a value of empty string.
export const fromText = ( text: string ) => ( text === '' ? null : text );
