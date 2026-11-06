import { __ } from '@wordpress/i18n';
import type { AreaTab } from './ui/AreaTabs';

// Every area the plugin owns. A tab is drawn only if its path is registered,
// so an area whose screen has not been built yet simply does not appear.
export const AREA_TABS: AreaTab[] = [
	{
		path: '/dwps-product-specifications/specification-sets',
		title: __( 'Specification sets', 'product-specifications' ),
	},
	{
		path: '/dwps-product-specifications/specifications',
		title: __( 'Specifications', 'product-specifications' ),
	},
	{
		path: '/dwps-product-specifications/import-export',
		title: __( 'Import / Export', 'product-specifications' ),
	},
	{
		path: '/dwps-product-specifications/settings',
		title: __( 'Settings', 'product-specifications' ),
	},
];
