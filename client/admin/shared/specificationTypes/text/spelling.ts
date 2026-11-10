// Two values are the same value when they differ only in letter case or in
// whitespace. The server applies the same rule.
export const valueKey = ( value: string ) =>
	value
		.replace( /\s+/gu, ' ' )
		.replace( /^ +| +$/g, '' )
		.toLowerCase();

// A value typed as a variant of a known one takes the known spelling.
export function knownSpelling( value: string, known: string[] ): string {
	const key = valueKey( value );

	return known.find( ( spelling ) => valueKey( spelling ) === key ) ?? value;
}

export function tokensFrom( tokens: string[], known: string[] ): string[] {
	const byKey = new Map< string, string >();

	for ( const token of tokens ) {
		const key = valueKey( token );

		if ( key !== '' && ! byKey.has( key ) ) {
			byKey.set( key, knownSpelling( token, known ).trim() );
		}
	}

	return Array.from( byKey.values() );
}
