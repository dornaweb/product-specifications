import type { Suggestion } from '../admin/shared/specificationTypes';

export type {
	StructureGroup,
	StructureItem,
} from '../admin/shared/contracts/structure';

export interface ValueDocument {
	schema_version: number;
	values: Record< string, unknown >;
}

export interface ProductValuesResponse {
	set: string | null;
	values: ValueDocument;
}

export interface SetRecord {
	id: number;
	title?: { raw?: string; rendered?: string } | string;
	slug: string;
	meta?: Record< string, unknown >;
}

export interface SpecificationRecord {
	id: number;
	name: string;
	slug: string;
	description?: string;
	suggestions?: Suggestion[];
	specification?: {
		type?: string;
		options?: Record< string, unknown >;
		default?: unknown;
	};
}
