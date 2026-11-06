export const asText = ( value: unknown ) =>
	typeof value === 'string' ? value : '';

// An empty control is the absence of a value, not a value of empty string.
export const fromText = ( text: string ) => ( text === '' ? null : text );
