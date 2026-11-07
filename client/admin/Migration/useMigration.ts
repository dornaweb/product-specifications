import { useCallback, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { fetchMigration, resumeMigration, startMigration } from './api';
import { screenState } from './screenState';
import type { MigrationStatus, StepScope } from './types';

const POLL_INTERVAL = 3000;

const messageOf = ( reason: unknown ) =>
	reason instanceof Error
		? reason.message
		: __( 'The migration request failed.', 'product-specifications' );

export function useMigration() {
	const [ status, setStatus ] = useState< MigrationStatus | null >( null );
	const [ scope, setScope ] = useState< StepScope[] >( [] );
	const [ error, setError ] = useState< string | null >( null );
	const [ isSubmitting, setIsSubmitting ] = useState( false );
	const [ polls, setPolls ] = useState( 0 );

	const apply = useCallback( ( next: MigrationStatus ) => {
		setStatus( next );
		setError( null );

		if ( next.steps ) {
			setScope( next.steps );
		}
	}, [] );

	const load = useCallback(
		() =>
			fetchMigration()
				.then( apply )
				.catch( ( reason ) => setError( messageOf( reason ) ) )
				.finally( () => setPolls( ( count ) => count + 1 ) ),
		[ apply ]
	);

	useEffect( () => {
		void load();
	}, [ load ] );

	const state = status ? screenState( status ) : null;

	// Each poll is armed only when the previous one has answered. A poll can run
	// a chunk itself on a site whose cron never fires, and two at once would
	// process the same chunk twice.
	useEffect( () => {
		if ( state !== 'running' ) {
			return;
		}

		const timer = window.setTimeout( () => void load(), POLL_INTERVAL );

		return () => window.clearTimeout( timer );
	}, [ state, polls, load ] );

	const submit = ( action: () => Promise< MigrationStatus > ) => {
		setIsSubmitting( true );
		action()
			.then( apply )
			.catch( ( reason ) => setError( messageOf( reason ) ) )
			.finally( () => setIsSubmitting( false ) );
	};

	return {
		status,
		state,
		scope,
		error,
		isSubmitting,
		load,
		start: () => submit( startMigration ),
		resume: () => submit( resumeMigration ),
		dismissError: () => setError( null ),
	};
}
