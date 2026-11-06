export interface SpecificationDefinitionPayload {
	type?: string;
	type_label?: string;
	options?: Record< string, unknown >;
	default?: unknown;
	usage_count?: number | null;
}

export interface SpecificationRecord {
	id: number;
	name?: string;
	slug?: string;
	description?: string;
	specification?: SpecificationDefinitionPayload;
}

export interface SpecificationItem {
	id: number;
	name: string;
	slug: string;
	description: string;
	type: string;
	typeLabel: string;
	options: Record< string, unknown >;
	defaultValue: unknown;
	usageCount: number;
	// Set on a specification saved in this sitting: it is pinned above the list
	// so the editor sees what they just made without the list reordering under
	// them.
	isJustAdded?: boolean;
}

export function mapRecord( record: SpecificationRecord ): SpecificationItem {
	const definition = record.specification ?? {};

	return {
		id: record.id,
		name: record.name ?? '',
		slug: record.slug ?? '',
		description: record.description ?? '',
		type: definition.type ?? 'text',
		typeLabel: definition.type_label ?? '',
		options: definition.options ?? {},
		defaultValue: definition.default ?? null,
		usageCount: definition.usage_count ?? 0,
	};
}
