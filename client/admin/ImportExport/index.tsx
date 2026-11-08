import { Card, CardBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { AREA_TABS } from '../shared/areas';
import { AreaTabs, PageHeader, PageShell } from '../shared/ui';
import ExportCard from './components/ExportCard';
import ImportCard from './components/ImportCard';
import './styles.scss';

const ImportExportPage = () => (
	<PageShell
		header={
			<PageHeader
				title={ __( 'Import / Export', 'product-specifications' ) }
				description={ __(
					'Move specifications, sets and product values between stores.',
					'product-specifications'
				) }
				tabs={ <AreaTabs tabs={ AREA_TABS } /> }
			/>
		}
	>
		<div className="ps-transfer">
			{ window.productSpecificationsAdmin?.migrationRequired ? (
				<Card>
					<CardBody>
						{ __(
							'Import and export are available once the migration is complete.',
							'product-specifications'
						) }
					</CardBody>
				</Card>
			) : (
				<>
					<ExportCard />
					<ImportCard />
				</>
			) }
		</div>
	</PageShell>
);

export default ImportExportPage;
