import {
	draftWithKey,
	draftWithName,
	isWholeNumber,
	messageOf,
	slugConflictFrom,
	SLUG_TAKEN_CODE,
} from './key';

describe( 'isWholeNumber', () => {
	it( 'matches only a canonical integer string', () => {
		expect( isWholeNumber( '2024' ) ).toBe( true );
		expect( isWholeNumber( '02024' ) ).toBe( false );
		expect( isWholeNumber( '2024-model' ) ).toBe( false );
		expect( isWholeNumber( '' ) ).toBe( false );
	} );
} );

describe( 'the key follows the name until it is edited', () => {
	const draft = { name: '', slug: '', slugEdited: false, other: 1 };

	it( 'derives a suffixed key from a name that is a whole number', () => {
		expect( draftWithName( draft, '2024' ).slug ).toBe( '2024-2' );
	} );

	it( 'derives the key from the name', () => {
		expect( draftWithName( draft, 'Screen Size' ) ).toMatchObject( {
			name: 'Screen Size',
			slug: 'screen-size',
			slugEdited: false,
			other: 1,
		} );
	} );

	it( 'keeps an edited key when the name changes', () => {
		const edited = draftWithKey( draft, 'display' );

		expect( edited ).toMatchObject( { slug: 'display', slugEdited: true } );
		expect( draftWithName( edited, 'Screen Size' ).slug ).toBe( 'display' );
	} );

	it( 'hands the key back to the name when the field is cleared', () => {
		const named = draftWithName( draft, 'Weight' );

		expect(
			draftWithKey( draftWithKey( named, 'mass' ), '' )
		).toMatchObject( { slug: 'weight', slugEdited: false } );
	} );
} );

describe( 'slugConflictFrom', () => {
	it( 'reads the server refusal and its suggestion', () => {
		expect(
			slugConflictFrom( {
				code: SLUG_TAKEN_CODE,
				message: 'taken',
				data: { status: 409, suggestion: 'weight-2' },
			} )
		).toEqual( { message: 'taken', suggestion: 'weight-2' } );
	} );

	it( 'ignores any other failure', () => {
		expect( slugConflictFrom( { code: 'rest_forbidden' } ) ).toBeNull();
		expect( slugConflictFrom( new Error( 'offline' ) ) ).toBeNull();
		expect(
			messageOf( { code: 'x', message: 'from rest' }, 'fallback' )
		).toBe( 'from rest' );
		expect( messageOf( 42, 'fallback' ) ).toBe( 'fallback' );
	} );
} );
