import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useState } from '@wordpress/element';

export interface PurgeEntry {
	slug: string;
	failure: string | null;
}

const POLL_INTERVAL = 3000;

// The values of a deleted specification held by many products are removed in the
// background. Each poll is armed only when the previous one has answered, and a
// poll nudges the server, so a site whose cron never fires still progresses.
export function usePurgeStatus() {
	const [ pending, setPending ] = useState< PurgeEntry[] >( [] );
	const [ polls, setPolls ] = useState( 0 );

	const refresh = useCallback(
		() =>
			apiFetch< { pending: PurgeEntry[] } >( {
				path: '/product-specifications/v2/purge',
			} )
				.then( ( response ) => setPending( response.pending ) )
				.catch( () => undefined )
				.finally( () => setPolls( ( count ) => count + 1 ) ),
		[]
	);

	useEffect( () => {
		void refresh();
	}, [ refresh ] );

	const active = pending.some( ( entry ) => entry.failure === null );

	useEffect( () => {
		if ( ! active ) {
			return;
		}

		const timer = window.setTimeout( () => void refresh(), POLL_INTERVAL );

		return () => window.clearTimeout( timer );
	}, [ active, polls, refresh ] );

	return { pending, active, refresh };
}
