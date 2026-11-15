// Group keys are stable within a set and are what an item's position is
// expressed against, so a new one is derived from its label and made unique
// against the keys already taken.
export function groupKeyFrom( label: string, taken: string[] ): string {
	const stem =
		label
			.toLowerCase()
			.replace( /[^a-z0-9]+/g, '-' )
			.replace( /^-+|-+$/g, '' ) || 'group';

	if ( ! taken.includes( stem ) ) {
		return stem;
	}

	let suffix = 2;

	while ( taken.includes( `${ stem }-${ suffix }` ) ) {
		suffix += 1;
	}

	return `${ stem }-${ suffix }`;
}
