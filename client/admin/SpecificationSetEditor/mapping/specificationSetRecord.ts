import { cleanForSlug } from '@wordpress/url';
import type {
	SpecificationSetGeneralState,
	SpecificationSetStatus,
} from '../state';

export interface SpecificationSetPostRecord {
	id?: number;
	title: string;
	slug?: string;
	content: string;
	status: SpecificationSetStatus;
}

export interface SpecificationSetEntityRecord {
	id: number;
	title?: string | {
		raw?: string;
		rendered?: string;
	};
	slug?: string;
	content?: string | {
		raw?: string;
		rendered?: string;
	};
	status?: string;
}

export function createSpecificationSetPostRecord(
	postId: number | null,
	general: SpecificationSetGeneralState
): SpecificationSetPostRecord {
	const slug = general.slug.trim();
	const generatedSlug = cleanForSlug( general.title );

	return {
		...( postId ? { id: postId } : {} ),
		...( slug || generatedSlug ? { slug: slug || generatedSlug } : {} ),
		title: general.title,
		content: general.description,
		status: general.status,
	};
}

export function createEmptySpecificationSetGeneralState(): SpecificationSetGeneralState {
	return {
		title: '',
		slug: '',
		description: '',
		status: 'draft',
	};
}

export function createSpecificationSetGeneralStateFromPostRecord(
	record: SpecificationSetEntityRecord
): SpecificationSetGeneralState {
	return {
		title: readTextField( record.title ),
		slug: record.slug ?? '',
		description: readTextField( record.content ),
		status: record.status === 'publish' ? 'publish' : 'draft',
	};
}

function readTextField(
	value:
		| string
		| {
				raw?: string;
				rendered?: string;
		  }
		| undefined
): string {
	if ( typeof value === 'string' ) {
		return value;
	}

	return value?.raw ?? stripTags( value?.rendered ?? '' );
}

function stripTags( value: string ): string {
	return value.replace( /<[^>]*>/g, '' );
}
