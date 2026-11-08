import { rejectedFields } from './validation';

describe( 'rejectedFields', () => {
	it( 'reads the messages of a rejected save', () => {
		expect(
			rejectedFields( {
				code: 'product_specifications_rejected_values',
				data: { status: 400, errors: { weight: 'Too heavy.' } },
			} )
		).toEqual( { weight: 'Too heavy.' } );
	} );

	it.each( [
		[ 'a failure without field messages', { data: { status: 500 } } ],
		[ 'a network failure', new Error( 'offline' ) ],
		[ 'nothing', null ],
	] )( 'reads no field messages from %s', ( _label, reason ) => {
		expect( rejectedFields( reason ) ).toEqual( {} );
	} );
} );
