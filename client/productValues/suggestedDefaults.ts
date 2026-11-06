import type { SpecificationRecord } from './types';

export interface SuggestedDefaultsResult {
	values: Record< string, unknown >;
	added: string[];
}

// A specification's default fills a product value only while that value is
// entirely absent, so an existing false/null/empty-string value is never
// treated as a gap to fill.
export function mergeSuggestedDefaults(
	current: Record< string, unknown >,
	records: SpecificationRecord[]
): SuggestedDefaultsResult {
	const values = { ...current };
	const added: string[] = [];

	for ( const record of records ) {
		const defaultValue = record.specification?.default;

		if (
			! Object.prototype.hasOwnProperty.call( values, record.slug ) &&
			defaultValue !== null &&
			defaultValue !== undefined &&
			defaultValue !== ''
		) {
			values[ record.slug ] = defaultValue;
			added.push( record.slug );
		}
	}

	return { values, added };
}
