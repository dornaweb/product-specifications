import { useMemo, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { SPECIFICATION_TAXONOMY } from '../../constants';
import { mapRecord } from '../../Specifications/mapRecord';
import type {
	SpecificationItem,
	SpecificationRecord,
} from '../../Specifications/mapRecord';
import type { ResolutionSelectors } from '../../shared/storeResolution';

const PER_PAGE = 100;

const BASE_QUERY = {
	page: 1,
	per_page: PER_PAGE,
	orderby: 'name',
	order: 'asc',
	context: 'edit',
} as const;

export interface LibraryEntry extends SpecificationItem {
	isInSet: boolean;
}

export interface SpecificationLibrary {
	search: string;
	setSearch: ( search: string ) => void;
	type: string;
	setType: ( type: string ) => void;
	hideUsed: boolean;
	setHideUsed: ( hide: boolean ) => void;
	entries: LibraryEntry[];
	total: number;
	hiddenCount: number;
	inSetCount: number;
	isLoading: boolean;
	// Names for what the set already holds, which the search or the type filter
	// may exclude from the column but never from the canvas.
	nameOf: ( slug: string ) => string | undefined;
}

/**
 * The specification catalog as the set editor needs it: a searchable column of
 * what can be added, and the names behind the slugs the structure stores.
 * @param usedSlugs
 * @param usedTermIds
 */
export function useSpecificationLibrary(
	usedSlugs: string[],
	usedTermIds: number[]
): SpecificationLibrary {
	const [ search, setSearch ] = useState( '' );
	const [ type, setType ] = useState( '' );
	const [ hideUsed, setHideUsed ] = useState( false );

	const listQuery = useMemo(
		() => ( search ? { ...BASE_QUERY, search } : BASE_QUERY ),
		[ search ]
	);

	// The structure holds term ids and slugs, not names. Asking for exactly the
	// ones in the set keeps the canvas readable whatever the column is showing.
	const heldQuery = useMemo(
		() => ( {
			...BASE_QUERY,
			include: [ ...usedTermIds ].sort( ( a, b ) => a - b ),
		} ),
		[ usedTermIds ]
	);

	const { records, total, held, isLoading } = useSelect(
		( select ) => {
			const store = select( coreStore );
			const resolution = store as unknown as ResolutionSelectors;
			const args = [ 'taxonomy', SPECIFICATION_TAXONOMY, listQuery ];

			return {
				records: ( store.getEntityRecords(
					'taxonomy',
					SPECIFICATION_TAXONOMY,
					listQuery
				) ?? [] ) as SpecificationRecord[],
				total:
					store.getEntityRecordsTotalItems(
						'taxonomy',
						SPECIFICATION_TAXONOMY,
						BASE_QUERY
					) ?? 0,
				held: ( ( heldQuery.include.length > 0
					? store.getEntityRecords(
							'taxonomy',
							SPECIFICATION_TAXONOMY,
							heldQuery
					  )
					: [] ) ?? [] ) as SpecificationRecord[],
				isLoading: ! resolution.hasFinishedResolution(
					'getEntityRecords',
					args
				),
			};
		},
		[ listQuery, heldQuery ]
	);

	const used = useMemo( () => new Set( usedSlugs ), [ usedSlugs ] );

	const entries = useMemo( () => {
		const mapped = records.map( ( record ) => ( {
			...mapRecord( record ),
			isInSet: used.has( record.slug ?? '' ),
		} ) );
		const byType = type
			? mapped.filter( ( entry ) => entry.type === type )
			: mapped;

		return hideUsed
			? byType.filter( ( entry ) => ! entry.isInSet )
			: byType;
	}, [ records, used, type, hideUsed ] );

	const names = useMemo( () => {
		const bySlug = new Map< string, string >();

		for ( const record of [ ...records, ...held ] ) {
			if ( record.slug ) {
				bySlug.set( record.slug, record.name ?? record.slug );
			}
		}

		return bySlug;
	}, [ records, held ] );

	return {
		search,
		setSearch,
		type,
		setType,
		hideUsed,
		setHideUsed,
		entries,
		total,
		hiddenCount: Math.max( 0, total - entries.length ),
		inSetCount: used.size,
		isLoading,
		nameOf: ( slug: string ) => names.get( slug ),
	};
}
