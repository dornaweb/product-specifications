// The one file that knows the admin shell provides navigation. Screens import
// from '../shared/platform', never from '@woocommerce/navigation'.
import {
	getNewPath,
	getPath,
	navigateTo,
	useQuery,
} from '@woocommerce/navigation';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export type AdminQuery = Record< string, string | undefined >;

export type AdminPathQuery = Record< string, string | number | undefined >;

let hasUnsavedChangesForNavigation = false;

function confirmDiscardUnsavedChanges(): boolean {
	// eslint-disable-next-line no-alert
	return window.confirm(
		__( 'Changes you made may not be saved.', 'product-specifications' )
	);
}

export function adminPath( path: string, query: AdminPathQuery = {} ): string {
	return getNewPath( query, path );
}

export function goToAdminPath(
	path: string,
	query: AdminPathQuery = {}
): void {
	if ( hasUnsavedChangesForNavigation && ! confirmDiscardUnsavedChanges() ) {
		return;
	}

	navigateTo( { url: adminPath( path, query ) } );
}

export function discardUnsavedChangesAndGoToAdminPath(
	path: string,
	query: AdminPathQuery = {}
): void {
	navigateTo( { url: adminPath( path, query ) } );
}

export function currentPath(): string {
	return getPath();
}

export function useAdminQuery(): AdminQuery {
	return useQuery() as AdminQuery;
}

export function useUnsavedChangesGuard( hasUnsavedChanges: boolean ): void {
	useEffect( () => {
		hasUnsavedChangesForNavigation = hasUnsavedChanges;

		if ( ! hasUnsavedChanges ) {
			return;
		}

		const warnBeforeUnload = ( event: BeforeUnloadEvent ) => {
			event.preventDefault();
			event.returnValue = '';
		};

		window.addEventListener( 'beforeunload', warnBeforeUnload, {
			capture: true,
		} );

		return () => {
			hasUnsavedChangesForNavigation = false;
			window.removeEventListener( 'beforeunload', warnBeforeUnload, {
				capture: true,
			} );
		};
	}, [ hasUnsavedChanges ] );
}
