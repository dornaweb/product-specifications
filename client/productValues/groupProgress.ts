import { specificationType } from '../admin/shared/specificationTypes';
import type { SpecificationRecord, StructureGroup } from './types';

export interface GroupProgress {
	filled: number;
	total: number;
}

export function groupProgress(
	group: StructureGroup,
	values: Record< string, unknown >,
	specifications: Record< number, SpecificationRecord >
): GroupProgress {
	const entered = group.items.filter(
		( item ) =>
			! specificationType(
				specifications[ item.term_id ]?.specification?.type ?? 'text'
			).readOnly
	);

	return {
		filled: entered.filter(
			( item ) =>
				Object.prototype.hasOwnProperty.call( values, item.slug ) &&
				values[ item.slug ] !== null
		).length,
		total: entered.length,
	};
}
