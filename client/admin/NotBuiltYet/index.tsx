import { __, sprintf } from '@wordpress/i18n';
import { blockTable } from '@wordpress/icons';
import { PageShell, PageHeader, EmptyState } from '../shared/ui';
import AreaTabs from '../shared/ui/AreaTabs';
import { AREA_TABS } from '../shared/areas';
import { useAdminQuery } from '../shared/platform';

// Placeholder for an area whose screen is not built. Delete this component once
// every area registers a screen of its own.
const NotBuiltYet = () => {
	const query = useAdminQuery();
	const area = AREA_TABS.find( ( tab ) => tab.path === query.path );
	const title =
		area?.title ?? __( 'Not built yet', 'product-specifications' );

	return (
		<PageShell
			header={
				<PageHeader
					title={ title }
					description={ __(
						'This area has not been built yet.',
						'product-specifications'
					) }
					tabs={ <AreaTabs tabs={ AREA_TABS } /> }
				/>
			}
		>
			<EmptyState
				icon={ blockTable }
				title={ sprintf(
					/* translators: %s: the name of the admin area. */
					__( '%s is not built yet', 'product-specifications' ),
					title
				) }
				copy={ __(
					'This page exists so the navigation can be reviewed before the screen behind it is built. It will be replaced by the real screen.',
					'product-specifications'
				) }
			/>
		</PageShell>
	);
};

export default NotBuiltYet;
