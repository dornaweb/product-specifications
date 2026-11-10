import { asText, fromText } from './textConversion';

describe( 'asText', () => {
	it( 'passes a string through', () => {
		expect( asText( 'hello' ) ).toBe( 'hello' );
	} );

	it.each( [
		[ 'a number', 5 ],
		[ 'null', null ],
		[ 'undefined', undefined ],
		[ 'a boolean', true ],
	] )(
		'renders %s as an empty control rather than coercing it',
		( _label, value ) => {
			expect( asText( value ) ).toBe( '' );
		}
	);
} );

describe( 'asText with a list', () => {
	it( 'joins a list of strings the way the storefront shows it', () => {
		expect( asText( [ 'USB-C', 'HDMI' ] ) ).toBe( 'USB-C, HDMI' );
	} );

	it( 'renders a list holding anything but strings as an empty control', () => {
		expect( asText( [ '6.1', 2 ] ) ).toBe( '' );
	} );
} );

describe( 'fromText', () => {
	it( 'reads an empty control as no value', () => {
		expect( fromText( '' ) ).toBeNull();
	} );

	it( 'passes a non-empty string through', () => {
		expect( fromText( 'hello' ) ).toBe( 'hello' );
	} );
} );
