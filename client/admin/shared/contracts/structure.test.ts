import { readStructure, slugBatches, structureSlugs } from './structure';

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

describe( 'slugBatches', () => {
	it( 'splits into requests the REST endpoint accepts', () => {
		const slugs = Array.from( { length: 205 }, ( _, i ) => `s${ i }` );
		const batches = slugBatches( slugs );

		expect( batches.map( ( batch ) => batch.length ) ).toEqual( [
			100, 100, 5,
		] );
		expect( batches.flat() ).toEqual( slugs );
		expect( slugBatches( [] ) ).toEqual( [] );
	} );

	it( 'keeps the query string of a batch of percent-encoded slugs short enough for the server', () => {
		const persian =
			'%d8%b3%db%8c%d8%b3%d8%aa%d9%85-%d8%b5%d9%88%d8%aa%db%8c';
		const slugs = Array.from(
			{ length: 66 },
			( _, i ) => `${ persian }-${ i }`
		);
		const batches = slugBatches( slugs );

		expect( batches.length ).toBeGreaterThan( 1 );
		expect( batches.flat() ).toEqual( slugs );
		batches.forEach( ( batch ) =>
			expect(
				batch.map( encodeURIComponent ).join( '' ).length
			).toBeLessThanOrEqual( 2000 )
		);
	} );

	it( 'gives a slug longer than the budget a batch of its own', () => {
		const long = '%d8%a8'.repeat( 400 );

		expect( slugBatches( [ 'a', long, 'b' ] ) ).toEqual( [
			[ 'a' ],
			[ long ],
			[ 'b' ],
		] );
	} );
} );
