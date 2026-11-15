export type FieldErrors = Record< string, string >;

export function rejectedFields( reason: unknown ): FieldErrors {
	const errors = ( reason as { data?: { errors?: unknown } } | null )?.data
		?.errors;

	if ( ! errors || typeof errors !== 'object' ) {
		return {};
	}

	return Object.fromEntries(
		Object.entries( errors ).filter(
			( entry ): entry is [ string, string ] =>
				typeof entry[ 1 ] === 'string'
		)
	);
}
