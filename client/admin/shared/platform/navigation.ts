// The one file that knows the admin shell provides navigation. Screens import
// from '../shared/platform', never from '@woocommerce/navigation'.
import {
	getNewPath,
	getPath,
	navigateTo,
	useQuery,
} from '@woocommerce/navigation';

export type AdminQuery = Record< string, string | undefined >;

export type AdminPathQuery = Record< string, string | number | undefined >;

export function adminPath( path: string, query: AdminPathQuery = {} ): string {
	return getNewPath( query, path );
}

export function goToAdminPath(
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
