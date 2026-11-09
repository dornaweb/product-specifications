import { __, _x } from '@wordpress/i18n';

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
		label: __( 'Length', 'product-specifications' ),
		units: same( 'mm', 'cm', 'm', 'km', 'in', 'ft', 'yd', 'mi' ),
	},
	{
		id: 'weight',
		label: __( 'Weight', 'product-specifications' ),
		units: same( 'mg', 'g', 'kg', 't', 'oz', 'lb' ),
	},
	{
		id: 'volume',
		label: __( 'Volume', 'product-specifications' ),
		units: [
			...same( 'ml', 'cl', 'l' ),
			{ id: 'm3', symbol: 'm³' },
			{ id: 'fl_oz', symbol: 'fl oz' },
			{ id: 'gal', symbol: 'gal' },
		],
	},
	{
		id: 'area',
		label: __( 'Area', 'product-specifications' ),
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
		label: __( 'Temperature', 'product-specifications' ),
		units: [
			{ id: 'celsius', symbol: '°C' },
			{ id: 'fahrenheit', symbol: '°F' },
			{ id: 'kelvin', symbol: 'K' },
		],
	},
	{
		id: 'data_size',
		label: __( 'Data size', 'product-specifications' ),
		units: same( 'B', 'kB', 'MB', 'GB', 'TB', 'PB' ),
	},
	{
		id: 'data_rate',
		label: __( 'Data rate', 'product-specifications' ),
		units: same( 'kbit/s', 'Mbit/s', 'Gbit/s' ),
	},
	{
		id: 'frequency',
		label: __( 'Frequency', 'product-specifications' ),
		units: same( 'Hz', 'kHz', 'MHz', 'GHz' ),
	},
	{
		id: 'power',
		label: __( 'Power', 'product-specifications' ),
		units: same( 'W', 'kW', 'hp' ),
	},
	{
		id: 'voltage',
		label: __( 'Voltage', 'product-specifications' ),
		units: same( 'V', 'kV' ),
	},
	{
		id: 'current',
		label: __( 'Current', 'product-specifications' ),
		units: same( 'mA', 'A' ),
	},
	{
		id: 'battery',
		label: __( 'Battery', 'product-specifications' ),
		units: same( 'mAh', 'Ah' ),
	},
	{
		id: 'energy',
		label: __( 'Energy', 'product-specifications' ),
		units: same( 'Wh', 'kWh' ),
	},
	{
		id: 'duration',
		label: __( 'Duration', 'product-specifications' ),
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
		label: __( 'Speed', 'product-specifications' ),
		units: same( 'km/h', 'mph', 'm/s' ),
	},
	{
		id: 'light',
		label: __( 'Light', 'product-specifications' ),
		units: [
			{ id: 'lm', symbol: 'lm' },
			{ id: 'cd_m2', symbol: 'cd/m²' },
		],
	},
	{
		id: 'sound',
		label: __( 'Sound', 'product-specifications' ),
		units: same( 'dB' ),
	},
];
