import { requiresUnits } from './numberOptions';

describe( 'requiresUnits', () => {
	it( 'accepts a plain number with no kind and no units', () => {
		expect( requiresUnits( {} ) ).toBeUndefined();
	} );

	it( 'asks for a unit once a kind is chosen', () => {
		expect( requiresUnits( { kind: 'length', units: [] } ) ).toBe(
			'Choose at least one unit.'
		);
	} );
} );
