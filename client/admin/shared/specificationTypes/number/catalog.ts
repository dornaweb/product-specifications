import { _x } from '@wordpress/i18n';

export interface Unit {
	id: string;
	symbol: string;
}

export interface UnitKind {
	id: string;
	label: string;
	units: Unit[];
}

export const CUSTOM_KIND = 'custom';

const same = ( ...ids: string[] ): Unit[] =>
	ids.map( ( id ) => ( { id, symbol: id } ) );

export const unitKinds = (): UnitKind[] => [
	{
		id: 'length',
		label: _x( 'Length', 'kind of measurement', 'product-specifications' ),
		units: same( 'mm', 'cm', 'm', 'km', 'in', 'ft', 'yd', 'mi' ),
	},
	{
		id: 'weight',
		label: _x( 'Weight', 'kind of measurement', 'product-specifications' ),
		units: same( 'mg', 'g', 'kg', 't', 'oz', 'lb' ),
	},
	{
		id: 'volume',
		label: _x( 'Volume', 'kind of measurement', 'product-specifications' ),
		units: [
			...same( 'ml', 'cl', 'l' ),
			{ id: 'm3', symbol: 'm³' },
			{ id: 'fl_oz', symbol: 'fl oz' },
			{ id: 'gal', symbol: 'gal' },
		],
	},
	{
		id: 'area',
		label: _x( 'Area', 'kind of measurement', 'product-specifications' ),
		units: [
			{ id: 'mm2', symbol: 'mm²' },
			{ id: 'cm2', symbol: 'cm²' },
			{ id: 'm2', symbol: 'm²' },
			{ id: 'in2', symbol: 'in²' },
			{ id: 'ft2', symbol: 'ft²' },
		],
	},
	{
		id: 'temperature',
		label: _x(
			'Temperature',
			'kind of measurement',
			'product-specifications'
		),
		units: [
			{ id: 'celsius', symbol: '°C' },
			{ id: 'fahrenheit', symbol: '°F' },
			{ id: 'kelvin', symbol: 'K' },
		],
	},
	{
		id: 'data_size',
		label: _x(
			'Data size',
			'kind of measurement',
			'product-specifications'
		),
		units: same( 'B', 'kB', 'MB', 'GB', 'TB', 'PB' ),
	},
	{
		id: 'data_rate',
		label: _x(
			'Data rate',
			'kind of measurement',
			'product-specifications'
		),
		units: same( 'kbit/s', 'Mbit/s', 'Gbit/s' ),
	},
	{
		id: 'frequency',
		label: _x(
			'Frequency',
			'kind of measurement',
			'product-specifications'
		),
		units: same( 'Hz', 'kHz', 'MHz', 'GHz' ),
	},
	{
		id: 'power',
		label: _x( 'Power', 'kind of measurement', 'product-specifications' ),
		units: same( 'W', 'kW', 'hp' ),
	},
	{
		id: 'voltage',
		label: _x( 'Voltage', 'kind of measurement', 'product-specifications' ),
		units: same( 'V', 'kV' ),
	},
	{
		id: 'current',
		label: _x( 'Current', 'kind of measurement', 'product-specifications' ),
		units: same( 'mA', 'A' ),
	},
	{
		id: 'battery',
		label: _x( 'Battery', 'kind of measurement', 'product-specifications' ),
		units: same( 'mAh', 'Ah' ),
	},
	{
		id: 'energy',
		label: _x( 'Energy', 'kind of measurement', 'product-specifications' ),
		units: same( 'Wh', 'kWh' ),
	},
	{
		id: 'duration',
		label: _x(
			'Duration',
			'kind of measurement',
			'product-specifications'
		),
		units: [
			...same( 's', 'min', 'h' ),
			{
				id: 'days',
				symbol: _x( 'days', 'unit symbol', 'product-specifications' ),
			},
			{
				id: 'months',
				symbol: _x( 'months', 'unit symbol', 'product-specifications' ),
			},
			{
				id: 'years',
				symbol: _x( 'years', 'unit symbol', 'product-specifications' ),
			},
		],
	},
	{
		id: 'speed',
		label: _x( 'Speed', 'kind of measurement', 'product-specifications' ),
		units: same( 'km/h', 'mph', 'm/s' ),
	},
	{
		id: 'light',
		label: _x( 'Light', 'kind of measurement', 'product-specifications' ),
		units: [
			{ id: 'lm', symbol: 'lm' },
			{ id: 'cd_m2', symbol: 'cd/m²' },
		],
	},
	{
		id: 'sound',
		label: _x( 'Sound', 'kind of measurement', 'product-specifications' ),
		units: same( 'dB' ),
	},
];
