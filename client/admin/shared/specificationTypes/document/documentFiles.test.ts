import { storedFiles, withLabel, withPicked } from './documentFiles';

describe( 'storedFiles', () => {
	it( 'reads files with and without a label', () => {
		expect(
			storedFiles( [ { id: 482, label: 'Datasheet' }, { id: 517 } ] )
		).toEqual( [ { id: 482, label: 'Datasheet' }, { id: 517 } ] );
	} );

	it.each( [
		[ 'a string', 'datasheet.pdf' ],
		[ 'null', null ],
		[ 'an object that is not a list', { id: 482 } ],
	] )( 'reads %s as no files', ( _label, value ) => {
		expect( storedFiles( value ) ).toEqual( [] );
	} );

	it( 'drops an entry without a usable id', () => {
		expect(
			storedFiles( [ { id: 0 }, { label: 'x' }, null, { id: 7 } ] )
		).toEqual( [ { id: 7 } ] );
	} );
} );

describe( 'withPicked', () => {
	it( 'adds a picked file once, after the listed ones', () => {
		expect( withPicked( [ { id: 1, label: 'A' } ], [ 1, 2, 2 ] ) ).toEqual(
			[ { id: 1, label: 'A' }, { id: 2 } ]
		);
	} );
} );

describe( 'withLabel', () => {
	it( 'leaves an emptied label out', () => {
		expect( withLabel( [ { id: 1, label: 'A' } ], 1, '' ) ).toEqual( [
			{ id: 1 },
		] );
	} );
} );
