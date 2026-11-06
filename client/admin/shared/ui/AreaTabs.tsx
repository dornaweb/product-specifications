import { __ } from '@wordpress/i18n';
import clsx from 'clsx';
import {
	adminPath,
	currentPath,
	goToAdminPath,
	isRegisteredPath,
} from '../platform';

export interface AreaTab {
	path: string;
	title: string;
	capability?: string;
	badge?: string;
}

interface Props {
	tabs: AreaTab[];
	capabilities?: string[];
}

// A tab is drawn only when its route is registered and the user holds the
// capability it names. An unregistered route rendered as a tab is a link to a
// permission error.
export function visibleTabs(
	tabs: AreaTab[],
	capabilities: string[],
	isRegistered: ( path: string ) => boolean = isRegisteredPath
): AreaTab[] {
	return tabs.filter( ( tab ) => {
		if ( ! isRegistered( tab.path ) ) {
			return false;
		}

		return ! tab.capability || capabilities.includes( tab.capability );
	} );
}

const AreaTabs = ( { tabs, capabilities = [] }: Props ) => {
	const shown = visibleTabs( tabs, capabilities );

	if ( shown.length < 2 ) {
		return null;
	}

	const active = currentPath();

	return (
		<nav
			className="ps-area-tabs"
			aria-label={ __( 'Areas', 'product-specifications' ) }
		>
			{ shown.map( ( tab ) => (
				<a
					key={ tab.path }
					href={ adminPath( tab.path ) }
					aria-current={ tab.path === active ? 'page' : undefined }
					className={ clsx( 'ps-area-tabs__tab', {
						'is-active': tab.path === active,
					} ) }
					onClick={ ( event ) => {
						event.preventDefault();
						goToAdminPath( tab.path );
					} }
				>
					{ tab.title }
					{ tab.badge && (
						<span className="ps-area-tabs__badge">
							{ tab.badge }
						</span>
					) }
				</a>
			) ) }
		</nav>
	);
};

export default AreaTabs;
