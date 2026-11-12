import type { View } from '@wordpress/dataviews';
import { defaultListView, isNarrowedView } from './listView';

describe( 'isNarrowedView', () => {
	it( 'is false for the default view', () => {
		expect( isNarrowedView( defaultListView() ) ).toBe( false );
	} );

	it.each( [
		[ 'a search', { search: 'screen' } ],
		[
			'a filter',
			{ filters: [ { field: 'type', operator: 'is', value: 'select' } ] },
		],
		[ 'a later page', { page: 2 } ],
	] )( 'is true for %s', ( _label, overrides ) => {
		expect(
			isNarrowedView( defaultListView( overrides as Partial< View > ) )
		).toBe( true );
	} );
} );
