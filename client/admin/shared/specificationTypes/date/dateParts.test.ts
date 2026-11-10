import { dateParts, monthValue } from './dateParts';

describe( 'dateParts', () => {
	it( 'reads a full date as its year and month', () => {
		expect( dateParts( '2026-03-14' ) ).toEqual( {
			year: '2026',
			month: '03',
		} );
	} );

	it( 'reads a year alone with no month', () => {
		expect( dateParts( '2026' ) ).toEqual( { year: '2026', month: '' } );
	} );
} );

describe( 'monthValue', () => {
	it( 'is null when nothing is entered', () => {
		expect( monthValue( { year: '', month: '' } ) ).toBeNull();
	} );

	it( 'keeps a half-entered month for the server to refuse', () => {
		expect( monthValue( { year: '', month: '03' } ) ).toBe( '-03' );
		expect( monthValue( { year: '2026', month: '' } ) ).toBe( '2026-' );
	} );

	it( 'reads back what it composes', () => {
		expect(
			dateParts( monthValue( { year: '2026', month: '03' } ) )
		).toEqual( { year: '2026', month: '03' } );
	} );
} );
