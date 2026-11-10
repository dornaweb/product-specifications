export interface DateParts {
	year: string;
	month: string;
}

const FULL_DATE = /^\d{4}-\d{2}-\d{2}$/;

export const fullDate = ( value: unknown ) =>
	typeof value === 'string' && FULL_DATE.test( value ) ? value : '';

export function dateParts( value: unknown ): DateParts {
	const [ year = '', month = '' ] =
		typeof value === 'string' ? value.split( '-' ) : [];

	return { year, month };
}

// A half-entered month is sent as typed, so the save is refused with a message
// rather than the entry being dropped.
export const monthValue = ( { year, month }: DateParts ) =>
	year === '' && month === '' ? null : `${ year }-${ month }`;
