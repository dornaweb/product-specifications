import { requiresSuggestions } from './textOptions';

describe( 'requiresSuggestions', () => {
	it( 'refuses suggestions with nothing to suggest', () => {
		expect( requiresSuggestions( { suggest: true } ) ).toBeDefined();
	} );

	it.each( [
		[ 'suggestions off', {} ],
		[ 'a typed suggestion', { suggest: true, values: [ 'Blue' ] } ],
		[ 'learning on', { suggest: true, learn: true } ],
	] )( 'accepts %s', ( _label, options ) => {
		expect( requiresSuggestions( options ) ).toBeUndefined();
	} );
} );
