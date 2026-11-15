import { parseNumber } from './parseNumber';

describe( 'parseNumber', () => {
	it.each( [
		[ '6,1', 6.1 ],
		[ '6.', 6 ],
		[ '-0.5', -0.5 ],
		[ '-', null ],
		[ '', null ],
	] )( 'reads %p as %p', ( text, expected ) => {
		expect( parseNumber( text ) ).toBe( expected );
	} );
} );
