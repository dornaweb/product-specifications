import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { inBatches } from '../admin/shared/contracts/structure';
import type {
	ProductValuesResponse,
	SetRecord,
	SpecificationRecord,
} from './types';

export const readProductValues = ( productId: number ) =>
	apiFetch< ProductValuesResponse >( {
		path: `/product-specifications/v2/products/${ productId }/specifications`,
	} );

export const saveProductValues = (
	productId: number,
	set: string | null,
	values: Record< string, unknown >
) =>
	apiFetch< ProductValuesResponse >( {
		path: `/product-specifications/v2/products/${ productId }/specifications`,
		method: 'POST',
		data: { set, values },
	} );

export const readSets = () =>
	apiFetch< SetRecord[] >( {
		path: '/wp/v2/specification-sets?context=edit&per_page=100&_fields=id,title,slug,meta',
	} );

// A set references its specifications by slug, and the terms endpoint answers at
// most a page of them at a time, so a large set is fetched in batches.
export const readSpecifications = ( slugs: string[] ) =>
	Promise.all(
		inBatches( slugs ).map( ( batch ) =>
			apiFetch< SpecificationRecord[] >( {
				path: addQueryArgs( '/wp/v2/specifications', {
					context: 'edit',
					per_page: batch.length,
					slug: batch,
					_fields:
						'id,name,slug,description,specification,suggestions',
				} ),
			} )
		)
	).then( ( results ) => results.flat() );
