export const STRUCTURE_META_KEY = 'specification_set_structure';
export const STRUCTURE_SCHEMA_VERSION = 1;

// The REST terms endpoint answers at most this many records per request.
export const REST_PAGE_LIMIT = 100;

export interface StructureItem {
	slug: string;
	visible: boolean;
}

export interface StructureGroup {
	key: string;
	label: string;
	description: string;
	visible: boolean;
	items: StructureItem[];
}

export interface SpecificationSetStructure {
	schema_version: number;
	groups: StructureGroup[];
}

export function emptyStructure(): SpecificationSetStructure {
	return { schema_version: STRUCTURE_SCHEMA_VERSION, groups: [] };
}

// The server drops a group with no label and an item with no slug, so anything
// unusable is discarded on the way in rather than shown as an entry the next
// save would silently remove. Members the stored document no longer carries,
// such as a term id, are left behind.
export function readStructure( value: unknown ): SpecificationSetStructure {
	if ( ! value || typeof value !== 'object' ) {
		return emptyStructure();
	}

	const groups = ( value as { groups?: unknown } ).groups;

	if ( ! Array.isArray( groups ) ) {
		return emptyStructure();
	}

	return {
		schema_version: STRUCTURE_SCHEMA_VERSION,
		groups: groups.map( readGroup ).filter( ( group ) => group !== null ),
	};
}

export function structureSlugs(
	structure: SpecificationSetStructure
): string[] {
	return Array.from(
		new Set(
			structure.groups.flatMap( ( group ) =>
				group.items.map( ( item ) => item.slug )
			)
		)
	);
}

export function inBatches< T >( entries: T[], size = REST_PAGE_LIMIT ): T[][] {
	const batches: T[][] = [];

	for ( let start = 0; start < entries.length; start += size ) {
		batches.push( entries.slice( start, start + size ) );
	}

	return batches;
}

function readGroup( value: unknown ): StructureGroup | null {
	if ( ! value || typeof value !== 'object' ) {
		return null;
	}

	const group = value as Record< string, unknown >;
	const key = String( group.key ?? '' );
	const label = String( group.label ?? '' );

	if ( key === '' || label === '' ) {
		return null;
	}

	return {
		key,
		label,
		description: String( group.description ?? '' ),
		visible: group.visible !== false,
		items: Array.isArray( group.items )
			? group.items.map( readItem ).filter( ( item ) => item !== null )
			: [],
	};
}

function readItem( value: unknown ): StructureItem | null {
	if ( ! value || typeof value !== 'object' ) {
		return null;
	}

	const item = value as Record< string, unknown >;
	const slug = typeof item.slug === 'string' ? item.slug : '';

	return slug === '' ? null : { slug, visible: item.visible !== false };
}
