import {
	Button,
	Card,
	CardBody,
	CardFooter,
	CardHeader,
	Notice as WordPressNotice,
	Spinner,
} from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { AREA_TABS } from '../shared/areas';
import { AreaTabs, Notice, PageHeader, PageShell } from '../shared/ui';
import RunLog from './components/RunLog';
import RunProgress from './components/RunProgress';
import ScopeSummary from './components/ScopeSummary';
import { useMigration } from './useMigration';
import './styles.scss';
import { SPECIFICATION_SETS_PATH } from '../shared/paths';

// A full load, not a route change: the Migration tab is registered when the
// page loads, so only a reload takes it away.
const leaveForSets = () => {
	const url = new URL( window.location.href );
	url.searchParams.set( 'path', SPECIFICATION_SETS_PATH );
	window.location.assign( url.toString() );
};

const MigrationPage = () => {
	const {
		status,
		state,
		scope,
		error,
		isSubmitting,
		load,
		start,
		resume,
		dismissError,
	} = useMigration();

	return (
		<PageShell
			header={
				<PageHeader
					title={ __( 'Migration', 'product-specifications' ) }
					description={ __(
						'Move your specifications to the new format.',
						'product-specifications'
					) }
					tabs={ <AreaTabs tabs={ AREA_TABS } /> }
				/>
			}
			notices={
				<Notice
					notice={
						error ? { status: 'error', message: error } : null
					}
					onDismiss={ dismissError }
				/>
			}
		>
			<div className="ps-migration">
				{ ! status && ! error && <Spinner /> }
				{ ! status && error && (
					<div>
						<Button
							variant="secondary"
							onClick={ () => void load() }
						>
							{ __( 'Try again', 'product-specifications' ) }
						</Button>
					</div>
				) }

				{ status && state === 'pending' && (
					<>
						<WordPressNotice
							status="warning"
							isDismissible={ false }
						>
							<b>
								{ __(
									'Back up your site before you start.',
									'product-specifications'
								) }
							</b>{ ' ' }
							{ __(
								'The migration changes data in place, and a backup is the only way back.',
								'product-specifications'
							) }
						</WordPressNotice>
						<Card>
							<CardHeader>
								<h2 className="ps-migration__title">
									{ __(
										'Ready to migrate',
										'product-specifications'
									) }
								</h2>
							</CardHeader>
							<CardBody className="ps-migration__body">
								<p>
									{ __(
										'This data is still in the version 1 format. Once it is migrated, your specification tables appear on your storefront again.',
										'product-specifications'
									) }
								</p>
								<ScopeSummary scope={ scope } />
							</CardBody>
							<CardFooter>
								<span className="ps-migration__help">
									{ __(
										'The migration never starts on its own.',
										'product-specifications'
									) }
								</span>
								<Button
									variant="primary"
									isBusy={ isSubmitting }
									disabled={ isSubmitting }
									onClick={ start }
								>
									{ __(
										'Start migration',
										'product-specifications'
									) }
								</Button>
							</CardFooter>
						</Card>
					</>
				) }

				{ status && state === 'running' && (
					<>
						<RunProgress run={ status.run } scope={ scope } />
						<p className="ps-migration__help">
							{ __(
								'You can leave this page. The migration keeps running, and you can come back any time to see where it is.',
								'product-specifications'
							) }
						</p>
						<RunLog run={ status.run } />
					</>
				) }

				{ status && state === 'failed' && (
					<>
						<WordPressNotice status="error" isDismissible={ false }>
							<b>
								{ sprintf(
									/* translators: %s: why the migration stopped. */
									__(
										'The migration stopped: %s',
										'product-specifications'
									),
									status.run.failure ?? ''
								) }
							</b>{ ' ' }
							{ __(
								'Nothing was lost. Resuming continues from where it stopped.',
								'product-specifications'
							) }
						</WordPressNotice>
						<RunProgress
							run={ status.run }
							scope={ scope }
							action={
								<Button
									variant="primary"
									isBusy={ isSubmitting }
									disabled={ isSubmitting }
									onClick={ resume }
								>
									{ __(
										'Resume migration',
										'product-specifications'
									) }
								</Button>
							}
						/>
						<RunLog run={ status.run } />
					</>
				) }

				{ status && state === 'complete' && (
					<>
						<WordPressNotice
							status={
								status.run.errors.length > 0
									? 'warning'
									: 'success'
							}
							isDismissible={ false }
						>
							{ status.run.errors.length > 0
								? __(
										'The migration finished, but some items could not be migrated. They are listed below; enter their specifications by hand.',
										'product-specifications'
								  )
								: __(
										'Your specification tables are live on your storefront again.',
										'product-specifications'
								  ) }
						</WordPressNotice>
						<Card>
							<CardHeader>
								<h2 className="ps-migration__title">
									{ __(
										'Migration complete',
										'product-specifications'
									) }
								</h2>
							</CardHeader>
							<CardBody className="ps-migration__body">
								<p>
									{ sprintf(
										/* translators: %s: how many items were migrated. */
										_n(
											'%s item was migrated.',
											'%s items were migrated.',
											status.run.processed,
											'product-specifications'
										),
										status.run.processed.toLocaleString()
									) }{ ' ' }
									{ __(
										'The version 1 data is kept, unused.',
										'product-specifications'
									) }
								</p>
							</CardBody>
							<CardFooter>
								<span className="ps-migration__help">
									{ __(
										'This summary is shown once. After you continue, the Migration tab is removed.',
										'product-specifications'
									) }
								</span>
								<Button
									variant="primary"
									onClick={ leaveForSets }
								>
									{ __( 'Done', 'product-specifications' ) }
								</Button>
							</CardFooter>
						</Card>
						<RunLog run={ status.run } />
					</>
				) }
			</div>
		</PageShell>
	);
};

export default MigrationPage;
