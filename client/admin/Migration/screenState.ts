import type { MigrationStatus, ScreenState } from './types';

// The data version decides whether the site is migrated. A run record saying
// `complete` on a site set back to version 1 is a finished run from before the
// rollback, and the site is waiting for a new one.
export function screenState( status: MigrationStatus ): ScreenState {
	if ( status.migrated ) {
		return 'complete';
	}

	if ( status.run.status === 'running' ) {
		return 'running';
	}

	if ( status.run.status === 'failed' ) {
		return 'failed';
	}

	return 'pending';
}
