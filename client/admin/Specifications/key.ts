import { cleanForSlug } from '@wordpress/url';

export const SLUG_TAKEN_CODE = 'product_specifications_slug_taken';
export const SLUG_INVALID_CODE = 'product_specifications_slug_invalid';

export interface SlugConflict {
	message: string;
	suggestion: string | null;
}

interface KeyedDraft {
	name: string;
	slug: string;
	slugEdited: boolean;
}

// PHP casts a canonical integer string to an integer array key, so the server
// refuses such a key and the form says so before the save.
export function isWholeNumber( key: string ): boolean {
	return /^-?\d+$/.test( key ) && String( Number( key ) ) === key;
}

// A name that is a whole number derives the first form the server would pick.
export function keyFrom( name: string ): string {
	const key = cleanForSlug( name );

	return isWholeNumber( key ) ? `${ key }-2` : key;
}

// The key follows the name until the merchant edits it.
export function draftWithName< T extends KeyedDraft >(
	draft: T,
	name: string
): T {
	return {
		...draft,
		name,
		slug: draft.slugEdited ? draft.slug : keyFrom( name ),
	};
}

// Clearing the field hands the key back to the name.
export function draftWithKey< T extends KeyedDraft >(
	draft: T,
	key: string
): T {
	if ( key === '' ) {
		return { ...draft, slug: keyFrom( draft.name ), slugEdited: false };
	}

	return { ...draft, slug: key, slugEdited: true };
}

export function slugConflictFrom( error: unknown ): SlugConflict | null {
	if ( ! error || typeof error !== 'object' ) {
		return null;
	}

	const { code, message, data } = error as {
		code?: unknown;
		message?: unknown;
		data?: { suggestion?: unknown };
	};

	if ( code !== SLUG_TAKEN_CODE && code !== SLUG_INVALID_CODE ) {
		return null;
	}

	return {
		message: typeof message === 'string' ? message : '',
		suggestion:
			typeof data?.suggestion === 'string' ? data.suggestion : null,
	};
}

// A REST failure arrives as a plain object with a message, not as an Error.
export function messageOf( error: unknown, fallback: string ): string {
	if ( error instanceof Error ) {
		return error.message;
	}

	if (
		error &&
		typeof error === 'object' &&
		'message' in error &&
		typeof error.message === 'string'
	) {
		return error.message;
	}

	return fallback;
}
