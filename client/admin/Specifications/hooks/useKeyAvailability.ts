import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

export interface KeyAvailability {
	checking: boolean;
	taken: boolean;
	heldBy: string | null;
	suggestion: string | null;
}

interface AvailabilityResponse {
	key: string | null;
	taken: boolean;
	holder: string | null;
	suggestion: string | null;
}

const FREE: KeyAvailability = {
	checking: false,
	taken: false,
	heldBy: null,
	suggestion: null,
};
const DEBOUNCE = 300;

// The server refuses a taken key on save; asking it first, with the same rules,
// lets the form say so while the merchant is still on the field and offer the
// key the server itself would suggest.
export function useKeyAvailability(
	key: string,
	enabled: boolean
): KeyAvailability {
	const [ state, setState ] = useState< KeyAvailability >( FREE );

	useEffect( () => {
		if ( ! enabled || key === '' ) {
			setState( FREE );
			return;
		}

		let cancelled = false;
		setState( { ...FREE, checking: true } );

		const timer = window.setTimeout( () => {
			apiFetch< AvailabilityResponse >( {
				path: addQueryArgs(
					'/product-specifications/v2/specifications/key-availability',
					{ key }
				),
			} )
				.then( ( response ) => {
					if ( ! cancelled ) {
						setState( {
							checking: false,
							taken: response.taken,
							heldBy: response.holder,
							suggestion: response.suggestion,
						} );
					}
				} )
				.catch( () => {
					if ( ! cancelled ) {
						setState( FREE );
					}
				} );
		}, DEBOUNCE );

		return () => {
			cancelled = true;
			window.clearTimeout( timer );
		};
	}, [ key, enabled ] );

	return state;
}
