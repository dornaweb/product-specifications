import { screenState } from './screenState';
import type { MigrationStatus, RunStatus } from './types';

const status = ( migrated: boolean, run: RunStatus ): MigrationStatus => ( {
	migrated,
	run: {
		status: run,
		step: 'values',
		cursor: 0,
		processed: 0,
		total: 0,
		started_at: null,
		skipped: [],
		errors: [],
		failure: null,
	},
	steps: null,
} );

describe( 'screenState', () => {
	it.each( [
		[ 'a migrated site', status( true, 'complete' ), 'complete' ],
		[
			'a migrated site with no run on record',
			status( true, 'idle' ),
			'complete',
		],
		[ 'a run in progress', status( false, 'running' ), 'running' ],
		[ 'a run that stopped', status( false, 'failed' ), 'failed' ],
		[ 'a site that has not started', status( false, 'idle' ), 'pending' ],
		[
			'a finished run on a site set back to version 1',
			status( false, 'complete' ),
			'pending',
		],
	] )( 'shows %s as %s', ( _label, given, expected ) => {
		expect( screenState( given ) ).toBe( expected );
	} );
} );
