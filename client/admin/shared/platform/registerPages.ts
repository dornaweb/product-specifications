import { addFilter } from '@wordpress/hooks';
import type { ComponentType } from 'react';

export interface AdminPage {
	container: ComponentType;
	path: string;
	breadcrumbs: string[];
	capability: string;
}

interface ShellPage extends AdminPage {
	isDefault: boolean;
	layout: { header: boolean };
	wpOpenMenu: string;
}

const NAMESPACE = 'dwps-product-specifications';
const PARENT_MENU = 'toplevel_page_woocommerce';

export function registerAdminPages( pages: AdminPage[] ): void {
	addFilter(
		'woocommerce_admin_pages_list',
		NAMESPACE,
		( registered: ShellPage[] ) => {
			pages.forEach( ( page ) => {
				registered.push( {
					...page,
					isDefault: true,
					layout: { header: false },
					wpOpenMenu: PARENT_MENU,
				} );
			} );

			return registered;
		}
	);
}
