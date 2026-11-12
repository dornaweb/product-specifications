import { specificationType, specificationTypes } from './index';

jest.mock( '@wordpress/hooks', () => ( {
	applyFilters: ( _hook: string, value: unknown ) => value,
} ) );

describe( 'specificationType', () => {
	it( 'resolves a registered id to its definition', () => {
		const resolved = specificationType( 'select' );

		expect( resolved.isRegistered ).toBe( true );
		expect( resolved.id ).toBe( 'select' );
	} );

	it( 'resolves an unregistered id to a named descriptor, not undefined', () => {
		const resolved = specificationType( 'rating' );

		expect( resolved.isRegistered ).toBe( false );
		expect( resolved.label ).toContain( 'rating' );
	} );

	it( 'carries every core type', () => {
		expect( Object.keys( specificationTypes() ) ).toEqual( [
			'text',
			'textarea',
			'select',
			'true_false',
			'unit',
			'woocommerce',
			'document',
			'link',
			'date',
		] );
	} );

	it( 'registers a product value control for every core type', () => {
		for ( const definition of Object.values( specificationTypes() ) ) {
			expect( definition.valueControl ).toBeDefined();
		}
	} );
} );
