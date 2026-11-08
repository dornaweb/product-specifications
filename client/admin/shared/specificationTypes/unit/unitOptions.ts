import type { Unit } from './catalog';

export const KIND = 'kind';
export const UNITS = 'units';
export const DEFAULT_UNIT = 'default_unit';
export const RANGE = 'range';
export const DECIMALS = 'decimals';
export const MIN = 'min';
export const MAX = 'max';
export const FALLBACK_DECIMALS = 2;

export interface UnitOptions {
	kind: string;
	units: Unit[];
	defaultUnit: string;
	range: boolean;
	decimals: number;
	min: number | null;
	max: number | null;
}

const bound = ( value: unknown ) =>
	typeof value === 'number' && Number.isFinite( value ) ? value : null;

export function unitOptions( options: Record< string, unknown > ): UnitOptions {
	const units = Array.isArray( options[ UNITS ] )
		? ( options[ UNITS ] as unknown[] ).filter(
				( unit ): unit is Unit =>
					typeof ( unit as Unit )?.id === 'string' &&
					typeof ( unit as Unit )?.symbol === 'string'
		  )
		: [];
	const stored =
		typeof options[ DEFAULT_UNIT ] === 'string'
			? ( options[ DEFAULT_UNIT ] as string )
			: '';

	return {
		kind:
			typeof options[ KIND ] === 'string'
				? ( options[ KIND ] as string )
				: '',
		units,
		defaultUnit: units.some( ( unit ) => unit.id === stored )
			? stored
			: units[ 0 ]?.id ?? '',
		range: options[ RANGE ] === true,
		decimals:
			typeof options[ DECIMALS ] === 'number'
				? ( options[ DECIMALS ] as number )
				: FALLBACK_DECIMALS,
		min: bound( options[ MIN ] ),
		max: bound( options[ MAX ] ),
	};
}
