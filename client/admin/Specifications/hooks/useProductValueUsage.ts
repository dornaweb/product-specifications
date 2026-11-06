import apiFetch from '@wordpress/api-fetch';
import { useEffect, useMemo, useState } from '@wordpress/element';
import type { SpecificationItem } from '../mapRecord';

interface ImpactResponse {
	product_count?: number;
}

export function useProductValueUsage( items: SpecificationItem[] ) {
	const [ bySpecification, setBySpecification ] = useState<
		Record< number, number >
	>( {} );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ hasError, setHasError ] = useState( false );
	const ids = useMemo( () => items.map( ( item ) => item.id ), [ items ] );

	useEffect( () => {
		let active = true;
		setIsLoading( true );
		setHasError( false );

		Promise.all(
			ids.map( async ( id ) => {
				const response = await apiFetch< ImpactResponse >( {
					path: `/product-specifications/v2/specifications/${ id }/deletion-impact`,
				} );
				return [ id, Number( response.product_count ?? 0 ) ] as const;
			} )
		)
			.then( ( entries ) => {
				if ( active ) {
					setBySpecification( Object.fromEntries( entries ) );
				}
			} )
			.catch( () => {
				if ( active ) {
					setHasError( true );
				}
			} )
			.finally( () => {
				if ( active ) {
					setIsLoading( false );
				}
			} );

		return () => {
			active = false;
		};
	}, [ ids ] );

	return {
		bySpecification,
		isLoading,
		hasError,
		total: Object.values( bySpecification ).reduce(
			( total, count ) => total + count,
			0
		),
	};
}
