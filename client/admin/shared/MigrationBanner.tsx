import { Notice } from '@wordpress/components';
import { MIGRATION_PATH } from './areas';
import { currentPath, goToAdminPath } from './platform';

// WooCommerce hides the admin notices WordPress prints on its React pages, so
// the migration notice is drawn here on every screen of the plugin.
const MigrationBanner = () => {
	const notice = window.dwspecsAdmin?.migrationNotice;

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
