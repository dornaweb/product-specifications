import valueKeys from '../../../../../tests/fixtures/value-keys.json';
import { knownSpelling, tokensFrom, valueKey } from './spelling';

describe( 'valueKey', () => {
	it.each( valueKeys.map( ( row ) => [ row.label, row.value, row.key ] ) )(
		'agrees with the server on %s',
		( _label, value, key ) => {
			expect( valueKey( value ) ).toBe( key );
		}
	);
} );

describe( 'knownSpelling', () => {
	it( 'gives a variant the known spelling', () => {
		expect( knownSpelling( ' blue ', [ 'Navy', 'Blue' ] ) ).toBe( 'Blue' );
	} );

	it( 'keeps a value that matches nothing as typed', () => {
		expect( knownSpelling( 'Black, matte', [ 'Blue' ] ) ).toBe(
			'Black, matte'
		);
	} );
} );

describe( 'tokensFrom', () => {
	it( 'drops a variant of a token already present', () => {
		expect( tokensFrom( [ 'HDMI', 'hdmi ', 'USB-C' ], [] ) ).toEqual( [
			'HDMI',
			'USB-C',
		] );
	} );

	it( 'gives each token the known spelling', () => {
		expect( tokensFrom( [ 'usb-c' ], [ 'USB-C' ] ) ).toEqual( [ 'USB-C' ] );
	} );

	it( 'drops an empty token', () => {
		expect( tokensFrom( [ ' ', 'HDMI' ], [] ) ).toEqual( [ 'HDMI' ] );
	} );
} );
