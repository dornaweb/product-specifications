import { inBatches, readStructure, structureSlugs } from './structure';

describe( 'readStructure', () => {
	it( 'keeps an item by its slug and leaves a stored term id behind', () => {
		const structure = readStructure( {
			schema_version: 1,
			groups: [
				{
					key: 'display',
					label: 'Display',
					items: [
						{ term_id: 12, slug: 'screen-size', visible: false },
						{ term_id: 13 },
						{ slug: '' },
					],
				},
			],
		} );

		expect( structure.groups[ 0 ].items ).toEqual( [
			{ slug: 'screen-size', visible: false },
		] );
	} );

	it( 'drops a group without a label and reads garbage as empty', () => {
		expect(
			readStructure( { groups: [ { key: 'a', items: [] } ] } ).groups
		).toEqual( [] );
		expect( readStructure( 'broken' ).groups ).toEqual( [] );
	} );
} );

describe( 'structureSlugs', () => {
	it( 'lists each slug once, across groups', () => {
		expect(
			structureSlugs(
				readStructure( {
					groups: [
						{
							key: 'a',
							label: 'A',
							items: [ { slug: 'x' }, { slug: 'y' } ],
						},
						{ key: 'b', label: 'B', items: [ { slug: 'x' } ] },
					],
				} )
			)
		).toEqual( [ 'x', 'y' ] );
	} );
} );

describe( 'inBatches', () => {
	it( 'splits into requests the REST endpoint accepts', () => {
		const slugs = Array.from( { length: 205 }, ( _, i ) => `s${ i }` );
		const batches = inBatches( slugs );

		expect( batches.map( ( batch ) => batch.length ) ).toEqual( [
			100, 100, 5,
		] );
		expect( batches.flat() ).toEqual( slugs );
		expect( inBatches( [] ) ).toEqual( [] );
	} );
} );
