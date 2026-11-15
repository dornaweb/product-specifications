import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { SPECIFICATION_TAXONOMY } from '../../constants';
import type { ResolutionSelectors } from '../../shared/storeResolution';

// One record is enough: the answer is in the collection's total, and a screen
// that only needs to know whether the library is empty should not fetch it.
const QUERY = { per_page: 1, _fields: 'id' } as const;

// `null` until the answer is known: a screen that branches on an empty library
// would otherwise show the first-run state for as long as the request takes.
export function useSpecificationCount(): number | null {
	return useSelect( ( select ) => {
		const store = select( coreStore );
		const resolution = store as unknown as ResolutionSelectors;
		const args = [ 'taxonomy', SPECIFICATION_TAXONOMY, QUERY ];

		store.getEntityRecords( 'taxonomy', SPECIFICATION_TAXONOMY, QUERY );

		if ( ! resolution.hasFinishedResolution( 'getEntityRecords', args ) ) {
			return null;
		}

		return (
			store.getEntityRecordsTotalItems(
				'taxonomy',
				SPECIFICATION_TAXONOMY,
				QUERY
			) ?? 0
		);
	}, [] );
}
