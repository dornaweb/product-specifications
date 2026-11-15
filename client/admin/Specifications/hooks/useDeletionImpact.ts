import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import type { SpecificationItem } from '../mapRecord';

export interface DeletionImpact {
	// Products holding a value, per specification id.
	products: Record< number, number >;
	total: number;
	isLoading: boolean;
	hasError: boolean;
}

// Counted only here, when the dialog opens: the answer changes a decision, and
// it is never shown on a list.
export function useDeletionImpact(
	items: SpecificationItem[]
): DeletionImpact {
	const [ impact, setImpact ] = useState< DeletionImpact >( {
		products: {},
		total: 0,
		isLoading: true,
		hasError: false,
	} );
	const ids = items.map( ( item ) => item.id ).join( ',' );

	useEffect( () => {
		let cancelled = false;
		setImpact( {
			products: {},
			total: 0,
			isLoading: true,
			hasError: false,
		} );

		Promise.all(
			ids
				.split( ',' )
				.filter( Boolean )
				.map( ( id ) =>
					apiFetch< { products: number } >( {
						path: `/product-specifications/v2/specifications/${ id }/impact`,
					} ).then(
						( response ) =>
							[ Number( id ), response.products ] as const
					)
				)
		)
			.then( ( counted ) => {
				if ( cancelled ) {
					return;
				}

				const products = Object.fromEntries( counted );

				setImpact( {
					products,
					total: counted.reduce(
						( sum, [ , count ] ) => sum + count,
						0
					),
					isLoading: false,
					hasError: false,
				} );
			} )
			.catch( () => {
				if ( ! cancelled ) {
					setImpact( {
						products: {},
						total: 0,
						isLoading: false,
						hasError: true,
					} );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ ids ] );

	return impact;
}
