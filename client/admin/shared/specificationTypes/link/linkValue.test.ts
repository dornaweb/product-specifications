import { enteredLink, storedLink } from './linkValue';

describe( 'storedLink', () => {
	it( 'reads both fields left empty as no value', () => {
		expect( storedLink( { url: '', label: '' } ) ).toBeNull();
	} );

	it( 'leaves out an empty label', () => {
		expect(
			storedLink( { url: 'https://example.com', label: '' } )
		).toEqual( { url: 'https://example.com' } );
	} );
} );

describe( 'enteredLink', () => {
	it( 'reads a bare stored address as a link without a label', () => {
		expect( enteredLink( 'https://example.com' ) ).toEqual( {
			url: 'https://example.com',
			label: '',
		} );
	} );
} );
