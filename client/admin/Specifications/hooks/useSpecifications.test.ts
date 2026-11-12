import { deletionResultMessage } from './deletionResult';
import type { SpecificationItem } from '../mapRecord';

const item = ( id: number, name: string ) =>
	( { id, name } ) as SpecificationItem;

describe( 'deletionResultMessage', () => {
	it( 'names successful and failed deletions', () => {
		expect(
			deletionResultMessage(
				[ item( 1, 'Screen size' ) ],
				[ item( 2, 'Weight' ) ]
			)
		).toBe( 'Deleted: Screen size. Not deleted: Weight.' );
	} );

	it( 'names every item when none could be deleted', () => {
		expect(
			deletionResultMessage( [], [ item( 1, 'Screen size' ) ] )
		).toBe( 'Nothing was deleted. Not deleted: Screen size.' );
	} );
} );
