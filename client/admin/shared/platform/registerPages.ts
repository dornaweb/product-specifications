import { addFilter } from '@wordpress/hooks';
import type { ComponentType } from 'react';
import { ADMIN_ROOT } from '../paths';

export interface AdminPage {
	container: ComponentType;
	path: string;
	breadcrumbs: string[];
	capability: string;
}

interface ShellPage extends AdminPage {
	isDefault: boolean;
	layout: { header: boolean };
	navArgs: { parentPath: string };
	wpOpenMenu: string;
}

const NAMESPACE = 'product-specifications';
const PARENT_MENU = 'toplevel_page_woocommerce';

const registeredPaths = new Set< string >();

export function isRegisteredPath( path: string ): boolean {
	return registeredPaths.has( path );
}

export function registerAdminPages( pages: AdminPage[] ): void {
	pages.forEach( ( page ) => registeredPaths.add( page.path ) );

	addFilter(
		'woocommerce_admin_pages_list',
		NAMESPACE,
		( registered: ShellPage[] ) => {
			pages.forEach( ( page ) => {
				registered.push( {
					...page,
					isDefault: true,
					layout: { header: false },
					navArgs: { parentPath: ADMIN_ROOT },
					wpOpenMenu: PARENT_MENU,
				} );
			} );

			return registered;
		}
	);
}
