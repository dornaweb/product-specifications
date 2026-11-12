import { useCallback, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { downloadReport } from './report';
import {
	discardImport,
	fetchImport,
	ImportRequestError,
	startImport,
	uploadPackage,
} from './api';
import type { ImportPayload, ImportRun } from './types';

const POLL_INTERVAL = 3000;

interface Failure {
	message: string;
	details: string[];
}

const failureOf = ( reason: unknown ): Failure =>
	reason instanceof ImportRequestError
		? { message: reason.message, details: reason.details }
		: {
				message: __(
					'The import request failed.',
					'product-specifications'
				),
				details: [],
		  };

export function useImport() {
	const [ run, setRun ] = useState< ImportRun | null >( null );
	const [ isLoaded, setIsLoaded ] = useState( false );
	const [ failure, setFailure ] = useState< Failure | null >( null );
	const [ isSubmitting, setIsSubmitting ] = useState( false );
	const [ polls, setPolls ] = useState( 0 );

	const apply = useCallback( ( payload: ImportPayload ) => {
		setRun( payload.run );
		setFailure( null );
		setIsLoaded( true );
	}, [] );

	const load = useCallback(
		() =>
			fetchImport()
				.then( apply )
				.catch( ( reason ) => setFailure( failureOf( reason ) ) )
				.finally( () => setPolls( ( count ) => count + 1 ) ),
		[ apply ]
	);

	useEffect( () => {
		void load();
	}, [ load ] );

	// A poll can run a chunk itself on a site whose cron never fires, so the next
	// one is armed only when the previous one has answered.
	const isRunning = run?.status === 'running';

	useEffect( () => {
		if ( ! isRunning ) {
			return;
		}

		const timer = window.setTimeout( () => void load(), POLL_INTERVAL );

		return () => window.clearTimeout( timer );
	}, [ isRunning, polls, load ] );

	const submit = ( action: () => Promise< ImportPayload > ) => {
		setIsSubmitting( true );
		action()
			.then( apply )
			.catch( ( reason ) => setFailure( failureOf( reason ) ) )
			.finally( () => setIsSubmitting( false ) );
	};

	return {
		run,
		isLoaded,
		failure,
		isSubmitting,
		upload: ( file: File ) => submit( () => uploadPackage( file ) ),
		start: () => submit( startImport ),
		discard: () => submit( discardImport ),
		downloadReport: () =>
			downloadReport().catch( ( reason ) =>
				setFailure( failureOf( reason ) )
			),
		dismissFailure: () => setFailure( null ),
	};
}
