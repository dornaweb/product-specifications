import { Notice } from '@wordpress/components';
import { currentPath, goToAdminPath } from './platform';
import { MIGRATION_PATH } from './paths';

// WooCommerce hides the admin notices WordPress prints on its React pages, so
// the migration notice is drawn here on every screen of the plugin.
const MigrationBanner = () => {
	const notice = window.productSpecificationsAdmin?.migrationNotice;

	if ( ! notice || currentPath() === MIGRATION_PATH ) {
		return null;
	}

	return (
		<Notice
			className="ps-migration-banner"
			status={ notice.status }
			isDismissible={ false }
			actions={ [
				{
					label: notice.action,
					variant: 'primary',
					onClick: () => goToAdminPath( MIGRATION_PATH ),
				},
			] }
		>
			{ notice.message }
		</Notice>
	);
};

export default MigrationBanner;
