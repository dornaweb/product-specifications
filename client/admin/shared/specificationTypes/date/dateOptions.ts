export const PRECISION = 'precision';

export type DatePrecision = 'day' | 'month' | 'year';

const PRECISIONS: DatePrecision[] = [ 'day', 'month', 'year' ];

export function datePrecision(
	options: Record< string, unknown >
): DatePrecision {
	const stored = options[ PRECISION ];

	return PRECISIONS.find( ( precision ) => precision === stored ) ?? 'day';
}
