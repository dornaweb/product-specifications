export const STRUCTURE_META_KEY = 'specification_set_structure';
export const STRUCTURE_SCHEMA_VERSION = 1;

export interface StructureItem {
	term_id: number;
	slug: string;
	visible: boolean;
	comparable: boolean;
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
// save would silently remove.
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
	const termId = Number( item.term_id ?? 0 );
	const slug = String( item.slug ?? '' );

	if ( ! Number.isInteger( termId ) || termId < 1 || slug === '' ) {
		return null;
	}

	return {
		term_id: termId,
		slug,
		visible: item.visible !== false,
		comparable: item.comparable === true,
	};
}

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
