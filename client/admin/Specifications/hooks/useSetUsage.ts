import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { SPECIFICATION_SET_POST_TYPE } from '../../constants';
import type { ResolutionSelectors } from '../../shared/storeResolution';
import type { SpecificationItem } from '../mapRecord';

interface SetRecord {
	id: number;
	title?: { rendered?: string; raw?: string } | string;
	meta?: {
		specification_set_structure?: {
			groups?: Array< { items?: Array< { slug?: string } > } >;
		};
	};
}

export interface SetUsage {
	// Set titles per specification id, so the delete dialog can name them.
	bySpecification: Record< number, string[] >;
	// Deduplicated across the whole selection: a sum over specifications would
	// count a set once per specification it holds, and a total that double
	// counts is one nobody trusts.
	setCount: number;
	isLoading: boolean;
	hasError: boolean;
}

const QUERY = { per_page: 100, status: 'any', context: 'edit' } as const;
const RESOLUTION_ARGS: unknown[] = [
	'postType',
	SPECIFICATION_SET_POST_TYPE,
	QUERY,
];

// Sets are tens of documents and hold the only record of what uses a
// specification, so usage is read here, when the answer changes a decision.
export function useSetUsage( items: SpecificationItem[] | null ): SetUsage {
	const { sets, isLoading, hasError } = useSelect( ( select ) => {
		const store = select( coreStore );
		const resolution = store as unknown as ResolutionSelectors;

		return {
			sets:
				( store.getEntityRecords(
					'postType',
					SPECIFICATION_SET_POST_TYPE,
					QUERY
				) as SetRecord[] | null ) ?? [],
			isLoading: ! resolution.hasFinishedResolution(
				'getEntityRecords',
				RESOLUTION_ARGS
			),
			hasError: resolution.hasResolutionFailed(
				'getEntityRecords',
				RESOLUTION_ARGS
			),
		};
	}, [] );

	return useMemo( () => {
		const bySpecification: Record< number, string[] > = {};
		const used = new Set< number >();

		for ( const item of items ?? [] ) {
			bySpecification[ item.id ] = [];
		}

		for ( const set of sets ) {
			const slugs = new Set(
				( set.meta?.specification_set_structure?.groups ?? [] ).flatMap(
					( group ) =>
						( group.items ?? [] ).map( ( entry ) => entry.slug )
				)
			);

			for ( const item of items ?? [] ) {
				if ( slugs.has( item.slug ) ) {
					bySpecification[ item.id ]?.push( setTitle( set ) );
					used.add( set.id );
				}
			}
		}

		return { bySpecification, setCount: used.size, isLoading, hasError };
	}, [ sets, items, isLoading, hasError ] );
}

function setTitle( set: SetRecord ): string {
	if ( typeof set.title === 'string' ) {
		return set.title;
	}

	return set.title?.raw ?? set.title?.rendered ?? '';
}
