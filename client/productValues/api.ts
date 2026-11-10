import apiFetch from '@wordpress/api-fetch';
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

const PER_PAGE = 100;

// The terms endpoint caps per_page at 100, so a set referencing more
// specifications than that is fetched in batches and merged.
export const readSpecifications = ( ids: number[] ) => {
	if ( ids.length === 0 ) {
		return Promise.resolve( [] as SpecificationRecord[] );
	}

	const batches: number[][] = [];
	for ( let start = 0; start < ids.length; start += PER_PAGE ) {
		batches.push( ids.slice( start, start + PER_PAGE ) );
	}

	return Promise.all(
		batches.map( ( batch ) =>
			apiFetch< SpecificationRecord[] >( {
				path: `/wp/v2/specifications?context=edit&per_page=${ PER_PAGE }&include=${ batch.join(
					','
				) }&_fields=id,name,slug,description,specification,suggestions`,
			} )
		)
	).then( ( results ) => results.flat() );
};
