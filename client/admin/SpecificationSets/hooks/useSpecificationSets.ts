import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { SPECIFICATION_SET_POST_TYPE } from '../../constants';

interface QueryState {
	page: number;
	per_page: number;
	orderby: string;
	order: 'asc' | 'desc';
}

interface SpecificationSetRecord {
	id: number;
	title: { rendered: string };
	date: string;
}

const DEFAULT_QUERY: QueryState = {
	page: 1,
	per_page: 20,
	orderby: 'title',
	order: 'asc',
};

export function useSpecificationSets() {
	const [ query, setQuery ] = useState< QueryState >( DEFAULT_QUERY );
	const [ selectedIds, setSelectedIds ] = useState< number[] >( [] );

	const queryArgs = {
		page: query.page,
		per_page: query.per_page,
		orderby: query.orderby,
		order: query.order,
		status: 'any',
	};

	const { records, total, isLoading } = useSelect(
		( select ) => {
			const store = select( coreStore );
			return {
				records:
					( store.getEntityRecords(
						'postType',
						SPECIFICATION_SET_POST_TYPE,
						queryArgs
					) as SpecificationSetRecord[] | null ) ?? [],
				total:
					store.getEntityRecordsTotalItems(
						'postType',
						SPECIFICATION_SET_POST_TYPE,
						queryArgs
					) ?? 0,
				isLoading: ! store.hasFinishedResolution(
					'getEntityRecords',
					[ 'postType', SPECIFICATION_SET_POST_TYPE, queryArgs ]
				),
			};
		},
		[ query ]
	);

	const onQueryChange =
		( param: string ) =>
		( value: string, _direction?: string ): void => {
			if ( param === 'paged' ) {
				setQuery( ( prev ) => ( { ...prev, page: Number( value ) } ) );
			} else if ( param === 'per_page' ) {
				setQuery( ( prev ) => ( {
					...prev,
					per_page: Number( value ),
					page: 1,
				} ) );
			} else if ( param === 'sort' ) {
				setQuery( ( prev ) => ( {
					...prev,
					orderby: value,
					order: ( _direction as 'asc' | 'desc' ) ?? 'asc',
					page: 1,
				} ) );
			}
		};

	const onSelectRow = ( id: number, checked: boolean ): void => {
		setSelectedIds( ( prev ) =>
			checked ? [ ...prev, id ] : prev.filter( ( i ) => i !== id )
		);
	};

	const onSelectAll = ( checked: boolean ): void => {
		setSelectedIds( checked ? records.map( ( r ) => r.id ) : [] );
	};

	return {
		records,
		total,
		isLoading,
		query,
		onQueryChange,
		selectedIds,
		onSelectRow,
		onSelectAll,
	};
}
