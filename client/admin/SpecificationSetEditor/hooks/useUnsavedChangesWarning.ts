import { useEffect } from 'react';

export function useUnsavedChangesWarning( hasUnsavedChanges: boolean ): void {
	useEffect( () => {
		if ( ! hasUnsavedChanges ) {
			return;
		}

		const warnBeforeUnload = ( event: BeforeUnloadEvent ) => {
			event.preventDefault();
			event.returnValue = '';
		};

		window.addEventListener( 'beforeunload', warnBeforeUnload );

		return () => {
			window.removeEventListener( 'beforeunload', warnBeforeUnload );
		};
	}, [ hasUnsavedChanges ] );
}
