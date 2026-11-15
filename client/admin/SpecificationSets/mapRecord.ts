import type { SpecificationSetItem } from './fields';
import {
	readStructure,
	STRUCTURE_META_KEY,
} from '../shared/contracts/structure';

export interface SpecificationSetRecord {
	id: number;
	title?: { rendered?: string; raw?: string } | string;
	status?: string;
	content?: { raw?: string } | string;
	// register_post_meta with show_in_rest exposes the structure under `meta`.
	meta?: Record< string, unknown >;
	product_count?: number | null;
}

// A REST record carries its structure as a nested document; the list shows two
// counts derived from it. Both are read from the set itself, never by scanning
// products.
export function mapRecord(
	record: SpecificationSetRecord
): SpecificationSetItem {
	const stored = record.meta?.[ STRUCTURE_META_KEY ];
	const structure = readStructure( stored );
	const { groups } = structure;

	return {
		id: record.id,
		title: readText( record.title ),
		status: record.status === 'publish' ? 'publish' : 'draft',
		description: readText( record.content ),
		structure: stored ? structure : null,
		groups: groups.length,
		specifications: groups.reduce(
			( total, group ) => total + group.items.length,
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
