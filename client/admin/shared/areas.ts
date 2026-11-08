import { __ } from '@wordpress/i18n';
import type { AreaTab } from './ui/AreaTabs';
import {
	IMPORT_EXPORT_PATH,
	MIGRATION_PATH,
	SETTINGS_PATH,
	SPECIFICATIONS_PATH,
	SPECIFICATION_SETS_PATH,
} from './paths';

// Every area the plugin owns. A tab is drawn only if its path is registered,
// so an area whose screen has not been built yet simply does not appear.
export const AREA_TABS: AreaTab[] = [
	{
		path: SPECIFICATION_SETS_PATH,
		title: __( 'Specification sets', 'product-specifications' ),
	},
	{
		path: SPECIFICATIONS_PATH,
		title: __( 'Specifications', 'product-specifications' ),
	},
	{
		path: IMPORT_EXPORT_PATH,
		title: __( 'Import / Export', 'product-specifications' ),
	},
	{
		path: SETTINGS_PATH,
		title: __( 'Settings', 'product-specifications' ),
	},
	{
		path: MIGRATION_PATH,
		title: __( 'Migration', 'product-specifications' ),
		needsAttention: true,
	},
];
