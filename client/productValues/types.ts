export interface ValueDocument {
	schema_version: number;
	values: Record< string, unknown >;
}

export interface ProductValuesResponse {
	set: string | null;
	values: ValueDocument;
}

export interface StructureItem {
	term_id: number;
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

export interface SetRecord {
	id: number;
	title?: { raw?: string; rendered?: string } | string;
	slug: string;
	meta?: {
		specification_set_structure?: {
			schema_version?: number;
			groups?: StructureGroup[];
		};
	};
}

export interface SpecificationRecord {
	id: number;
	name: string;
	slug: string;
	description?: string;
	specification?: {
		type?: string;
		options?: Record< string, unknown >;
		default?: unknown;
	};
}
