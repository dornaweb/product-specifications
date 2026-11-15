import { lazy, type ComponentType } from 'react';
import { registerAdminPages } from './shared/platform';
import {
	ADMIN_ROOT,
	EDIT_SPECIFICATION_SET_PATH,
	IMPORT_EXPORT_PATH,
	MIGRATION_PATH,
	NEW_SPECIFICATION_SET_PATH,
	SETTINGS_PATH,
	SPECIFICATIONS_PATH,
	SPECIFICATION_SETS_PATH,
} from './shared/paths';

import './shared/ui/styles.scss';

// Page containers registered with the admin shell must be lazy-loaded.
// Do not statically import page containers.
// Static imports break code splitting and does not work with imported CSS files

const MainPage = lazy(
	() => import( /* webpackChunkName: "main" */ './Main' )
);

const SpecificationSetsPage = lazy(
	() =>
		import(
			/* webpackChunkName: "specification-sets" */ './SpecificationSets'
		)
);

const SpecificationsPage = lazy(
	() => import( /* webpackChunkName: "specifications" */ './Specifications' )
);

const SpecificationSetEditorPage = lazy(
	() =>
		import(
			/* webpackChunkName: "specification-set-editor" */ './SpecificationSetEditor'
		)
);

const SettingsPage = lazy(
	() => import( /* webpackChunkName: "settings" */ './Settings' )
);

const MigrationPage = lazy(
	() => import( /* webpackChunkName: "migration" */ './Migration' )
);

const ImportExportPage = lazy(
	() => import( /* webpackChunkName: "import-export" */ './ImportExport' )
);

const containers: Record< string, ComponentType > = {
	[ ADMIN_ROOT ]: MainPage,
	[ SPECIFICATION_SETS_PATH ]: SpecificationSetsPage,
	[ NEW_SPECIFICATION_SET_PATH ]: SpecificationSetEditorPage,
	[ EDIT_SPECIFICATION_SET_PATH ]: SpecificationSetEditorPage,
	[ SPECIFICATIONS_PATH ]: SpecificationsPage,
	[ IMPORT_EXPORT_PATH ]: ImportExportPage,
	[ SETTINGS_PATH ]: SettingsPage,
	[ MIGRATION_PATH ]: MigrationPage,
};

// The server decides which screens exist and who may open them.
registerAdminPages(
	( window.productSpecificationsAdmin?.pages ?? [] ).flatMap( ( screen ) => {
		const container = containers[ screen.path ];

		return container
			? [
					{
						container,
						path: screen.path,
						breadcrumbs: [ screen.title ],
						capability: screen.capability,
					},
			  ]
			: [];
	} )
);
