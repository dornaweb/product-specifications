import { useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { View } from '@wordpress/dataviews';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { SPECIFICATION_TAXONOMY } from '../../constants';
import { mapRecord } from '../mapRecord';
import type { SpecificationItem, SpecificationRecord } from '../mapRecord';
import { defaultView } from '../fields';
import type {
	ResolutionActions,
	ResolutionSelectors,
} from '../../shared/storeResolution';
import type { NoticeState } from '../../shared/ui';
import { deletionResultMessage } from './deletionResult';

const ENTITY = [ 'taxonomy', SPECIFICATION_TAXONOMY ] as const;

interface TaxonomyActions extends ResolutionActions {
	saveEntityRecord: (
		kind: string,
		name: string,
		record: Record< string, unknown >,
		options?: { throwOnError?: boolean }
	) => Promise< SpecificationRecord >;
	deleteEntityRecord: (
		kind: string,
		name: string,
		key: number,
		query?: { force?: boolean },
		options?: { throwOnError?: boolean }
	) => Promise< unknown >;
}

export function useSpecifications() {
	const [ view, setViewState ] = useState< View >( defaultView );
	const [ notice, setNotice ] = useState< NoticeState | null >( null );
	const [ justAdded, setJustAdded ] = useState< SpecificationItem[] >( [] );
	const typeFilter = useMemo( () => {
		const filter = view.filters?.find(
			( entry ) => entry.field === 'type'
		);

		return typeof filter?.value === 'string' ? filter.value : null;
	}, [ view ] );

	const queryArgs = useMemo( () => {
		const args: Record< string, string | number > = {
			page: view.page ?? 1,
			per_page: view.perPage ?? 20,
			orderby: 'name',
			order: view.sort?.direction ?? 'asc',
			context: 'edit',
		};

		if ( view.search ) {
			args.search = view.search;
		}

		if ( typeFilter ) {
			args.specification_type = typeFilter;
		}

		return args;
	}, [ view, typeFilter ] );

	const resolutionArgs = useMemo(
		() => [ ...ENTITY, queryArgs ] as const,
		[ queryArgs ]
	);

	const { records, totalItems, totalPages, isLoading, hasError } = useSelect(
		( select ) => {
			const store = select( coreStore );
			const resolution = store as unknown as ResolutionSelectors;

			return {
				records: store.getEntityRecords( ...resolutionArgs ) ?? [],
				totalItems:
					store.getEntityRecordsTotalItems( ...resolutionArgs ) ?? 0,
				totalPages:
					store.getEntityRecordsTotalPages( ...resolutionArgs ) ?? 0,
				isLoading: ! resolution.hasFinishedResolution(
					'getEntityRecords',
					[ ...resolutionArgs ]
				),
				hasError: resolution.hasResolutionFailed( 'getEntityRecords', [
					...resolutionArgs,
				] ),
			};
		},
		[ resolutionArgs ]
	);

	const { invalidateResolution, saveEntityRecord, deleteEntityRecord } =
		useDispatch( coreStore ) as unknown as TaxonomyActions;

	// A specification saved in this sitting is pinned above the list. Sorted by
	// name it would land anywhere — past the first page in a library of any
	// size — and the editor would have no sign that the save landed.
	const data: SpecificationItem[] = useMemo( () => {
		const pinned = new Set( justAdded.map( ( item ) => item.id ) );
		const mapped = ( records as SpecificationRecord[] )
			.map( mapRecord )
			.filter( ( item ) => ! pinned.has( item.id ) );
		return [ ...justAdded, ...mapped ];
	}, [ records, justAdded ] );

	const refresh = () =>
		invalidateResolution( 'getEntityRecords', [ ...resolutionArgs ] );

	const run = async < T >(
		work: () => Promise< T >,
		message: string
	): Promise< T | null > => {
		try {
			const result = await work();
			await refresh();
			setNotice( { status: 'success', message } );

			return result;
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message:
					error instanceof Error
						? error.message
						: __(
								'Something went wrong.',
								'product-specifications'
						  ),
			} );

			return null;
		}
	};

	return {
		view,
		// Narrowing the list answers a different question, and rows pinned to
		// the top of the previous one would contradict the search that follows.
		setView: ( next: View ) => {
			setJustAdded( [] );
			setViewState( next );
		},
		justAddedCount: justAdded.length,
		data,
		isLoading,
		hasError,
		notice,
		setNotice,
		dismissNotice: () => setNotice( null ),
		retry: refresh,
		paginationInfo: { totalItems, totalPages },
		create: async ( record: Record< string, unknown > ) => {
			const saved = await run(
				() =>
					saveEntityRecord( ...ENTITY, record, {
						throwOnError: true,
					} ),
				__( 'Specification created.', 'product-specifications' )
			);

			if ( saved ) {
				setJustAdded( ( pinned ) => [
					{ ...mapRecord( saved ), isJustAdded: true },
					...pinned,
				] );
			}

			return saved;
		},
		update: async ( id: number, record: Record< string, unknown > ) => {
			const saved = await run(
				() =>
					saveEntityRecord(
						...ENTITY,
						{ id, ...record },
						{ throwOnError: true }
					),
				__( 'Specification saved.', 'product-specifications' )
			);

			if ( saved ) {
				setJustAdded( ( pinned ) =>
					pinned.map( ( item ) =>
						item.id === id
							? { ...mapRecord( saved ), isJustAdded: true }
							: item
					)
				);
			}

			return saved;
		},
		remove: async ( items: SpecificationItem[] ) => {
			const removed = new Set( items.map( ( item ) => item.id ) );

			setJustAdded( ( pinned ) =>
				pinned.filter( ( item ) => ! removed.has( item.id ) )
			);

			const deleted: SpecificationItem[] = [];
			const failed: SpecificationItem[] = [];

			for ( const item of items ) {
				try {
					await deleteEntityRecord(
						...ENTITY,
						item.id,
						{ force: true },
						{ throwOnError: true }
					);
					deleted.push( item );
				} catch {
					failed.push( item );
				}
			}

			await refresh();

			if ( failed.length === 0 ) {
				setNotice( {
					status: 'success',
					message: __(
						'Specifications deleted.',
						'product-specifications'
					),
				} );

				return true;
			}

			setNotice( {
				status: 'error',
				message: deletionResultMessage( deleted, failed ),
			} );

			return false;
		},
	};
}
