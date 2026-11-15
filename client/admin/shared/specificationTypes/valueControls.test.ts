import { toTriState, fromTriState } from './valueControls';

describe( 'toTriState', () => {
	it.each( [
		[ true, 'yes' ],
		[ false, 'no' ],
		[ null, '' ],
		[ undefined, '' ],
	] )( 'renders %s as %s', ( value, expected ) => {
		expect( toTriState( value ) ).toBe( expected );
	} );
} );

describe( 'fromTriState', () => {
	it.each( [
		[ 'yes', true ],
		[ 'no', false ],
		[ '', null ],
	] )( 'reads %s as %s', ( next, expected ) => {
		expect( fromTriState( next ) ).toBe( expected );
	} );
} );
