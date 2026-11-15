export function parseNumber( text: string ): number | null {
	const normalized = text.trim().replace( ',', '.' ).replace( /\.$/, '' );

	if ( ! /^-?(\d+(\.\d+)?|\.\d+)$/.test( normalized ) ) {
		return null;
	}

	return Number( normalized );
}
