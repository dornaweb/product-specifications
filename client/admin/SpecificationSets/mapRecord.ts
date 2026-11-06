import type { SpecificationSetItem } from './fields';

interface Structure {
	groups?: Array< { items?: unknown[] } >;
}

export interface SpecificationSetRecord {
	id: number;
	title?: { rendered?: string; raw?: string } | string;
	status?: string;
	content?: { raw?: string } | string;
	// register_post_meta with show_in_rest exposes the structure under `meta`.
	meta?: { specification_set_structure?: Structure };
	product_count?: number | null;
}

// A REST record carries its structure as a nested document; the list shows two
// counts derived from it. Both are read from the set itself, never by scanning
// products.
export function mapRecord(
	record: SpecificationSetRecord
): SpecificationSetItem {
	const groups = record.meta?.specification_set_structure?.groups ?? [];

	return {
		id: record.id,
		title: readText( record.title ),
		status: record.status === 'publish' ? 'publish' : 'draft',
		description: readText( record.content ),
		structure: record.meta?.specification_set_structure ?? null,
		groups: groups.length,
		specifications: groups.reduce(
			( total, group ) => total + ( group.items?.length ?? 0 ),
			0
		),
		products: record.product_count ?? 0,
	};
}

function readText(
	value: { rendered?: string; raw?: string } | string | undefined
): string {
	if ( typeof value === 'string' ) {
		return value;
	}

	return value?.raw ?? value?.rendered ?? '';
}
