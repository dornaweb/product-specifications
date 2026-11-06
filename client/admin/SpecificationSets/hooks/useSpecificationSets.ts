import { useState, useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import type { View } from '@wordpress/dataviews';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { SPECIFICATION_SET_POST_TYPE } from '../../constants';
import { mapRecord } from '../mapRecord';
import type { SpecificationSetRecord } from '../mapRecord';
import { defaultView } from '../fields';
import type {
	ResolutionActions,
	ResolutionSelectors,
} from '../../shared/storeResolution';
import type { SpecificationSetItem, SpecificationSetStatus } from '../fields';
import type { NoticeState } from '../../shared/ui';

export function useSpecificationSets() {
	const [ view, setView ] = useState< View >( defaultView );

	// An `undefined` value still creates a key, and core-data keys its cache by
	// the whole object — so leaving `search: undefined` in produces a different
	// query from the one that resolves, and the list stays empty.
	const queryArgs = useMemo( () => {
		const args: Record< string, string | number > = {
			page: view.page ?? 1,
			per_page: view.perPage ?? 20,
			orderby: view.sort?.field === 'title' ? 'title' : 'date',
			order: view.sort?.direction ?? 'asc',
			status: 'any',
		};

		if ( view.search ) {
			args.search = view.search;
		}

		const statusFilter = view.filters?.find(
			( filter ) => filter.field === 'status'
		);

		if ( typeof statusFilter?.value === 'string' ) {
			args.status = statusFilter.value;
		}

		return args;
	}, [ view ] );

	const resolutionArgs = useMemo(
		() => [ 'postType', SPECIFICATION_SET_POST_TYPE, queryArgs ] as const,
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
		useDispatch( coreStore ) as unknown as ResolutionActions & {
			saveEntityRecord: (
				kind: string,
				name: string,
				record: Record< string, unknown >,
				options?: { throwOnError?: boolean }
			) => Promise< { id?: number } >;
			deleteEntityRecord: (
				kind: string,
				name: string,
				key: number,
				query?: { force?: boolean },
				options?: { throwOnError?: boolean }
			) => Promise< unknown >;
		};
	const [ notice, setNotice ] = useState< NoticeState | null >( null );

	const refresh = () =>
		invalidateResolution( 'getEntityRecords', [ ...resolutionArgs ] );

	const run = async ( work: () => Promise< void >, message: string ) => {
		try {
			await work();
			await refresh();
			setNotice( { status: 'success', message } );
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
		}
	};

	const handlers = {
		duplicate: ( item: SpecificationSetItem ) =>
			run(
				async () => {
					await saveEntityRecord(
						'postType',
						SPECIFICATION_SET_POST_TYPE,
						{
							title: sprintf(
								/* translators: %s: the name of the set being copied. */
								__( '%s (copy)', 'product-specifications' ),
								item.title
							),
							content: item.description,
							status: 'draft',
							meta: {
								specification_set_structure: item.structure,
							},
						},
						{ throwOnError: true }
					);
				},
				__( 'Specification set duplicated.', 'product-specifications' )
			),
		remove: ( items: SpecificationSetItem[] ) =>
			run(
				async () => {
					for ( const item of items ) {
						await deleteEntityRecord(
							'postType',
							SPECIFICATION_SET_POST_TYPE,
							item.id,
							undefined,
							{ throwOnError: true }
						);
					}
				},
				__( 'Specification sets deleted.', 'product-specifications' )
			),
		setStatus: (
			items: SpecificationSetItem[],
			status: SpecificationSetStatus
		) =>
			run(
				async () => {
					for ( const item of items ) {
						await saveEntityRecord(
							'postType',
							SPECIFICATION_SET_POST_TYPE,
							{ id: item.id, status },
							{ throwOnError: true }
						);
					}
				},
				__( 'Status updated.', 'product-specifications' )
			),
	};

	const data: SpecificationSetItem[] = useMemo(
		() =>
			( records as SpecificationSetRecord[] ).map( ( record ) =>
				mapRecord( record )
			),
		[ records ]
	);

	return {
		view,
		setView,
		data,
		isLoading,
		hasError,
		retry: refresh,
		notice,
		dismissNotice: () => setNotice( null ),
		handlers,
		paginationInfo: { totalItems, totalPages },
	};
}
