import { presentationForNewSet, readPresentation } from './presentation';

describe( 'readPresentation', () => {
	it( 'reads a missing or malformed document as comparison off', () => {
		expect( readPresentation( undefined ).comparison ).toBe( false );
		expect( readPresentation( 'broken' ).comparison ).toBe( false );
		expect( readPresentation( { comparison: 'yes' } ).comparison ).toBe(
			false
		);
	} );

	it( 'keeps known members and replaces unknown values', () => {
		expect(
			readPresentation( {
				schema_version: 4,
				empty_rows: 'show',
				empty_groups: 'sometimes',
				comparison: true,
			} )
		).toEqual( {
			schema_version: 1,
			empty_rows: 'show',
			empty_groups: 'hide',
			comparison: true,
		} );
	} );

	it( 'starts a new set with comparison on', () => {
		expect( presentationForNewSet().comparison ).toBe( true );
	} );
} );
