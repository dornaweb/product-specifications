import { mergeSuggestedDefaults } from './suggestedDefaults';
import type { SpecificationRecord } from './types';

const record = (
	slug: string,
	defaultValue: unknown
): SpecificationRecord => ( {
	id: 1,
	name: slug,
	slug,
	specification: { default: defaultValue },
} );

describe( 'mergeSuggestedDefaults', () => {
	it( 'fills a missing value with the default and reports it as added', () => {
		const result = mergeSuggestedDefaults( {}, [
			record( 'screen', '15 inch' ),
		] );

		expect( result.values ).toEqual( { screen: '15 inch' } );
		expect( result.added ).toEqual( [ 'screen' ] );
	} );

	it.each( [
		[ 'an explicit false', false ],
		[ 'an explicit null', null ],
		[ 'a stored string value', 'already set' ],
	] )( 'does not overwrite %s already present', ( _label, stored ) => {
		const result = mergeSuggestedDefaults( { screen: stored }, [
			record( 'screen', '15 inch' ),
		] );

		expect( result.values ).toEqual( { screen: stored } );
		expect( result.added ).toEqual( [] );
	} );

	it.each( [
		[ 'null', null ],
		[ 'undefined', undefined ],
		[ 'an empty string', '' ],
	] )( 'treats %s as no default to suggest', ( _label, defaultValue ) => {
		const result = mergeSuggestedDefaults( {}, [
			record( 'screen', defaultValue ),
		] );

		expect( result.values ).toEqual( {} );
		expect( result.added ).toEqual( [] );
	} );
} );
