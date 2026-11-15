import {
	Button,
	Card,
	CardBody,
	CardFooter,
	CardHeader,
	Notice,
	ProgressBar,
	Spinner,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useImport } from '../import/useImport';
import OutcomeSummary from './OutcomeSummary';

const ImportCard = () => {
	const {
		run,
		isLoaded,
		failure,
		isSubmitting,
		upload,
		start,
		discard,
		downloadReport,
		dismissFailure,
	} = useImport();
	const [ file, setFile ] = useState< File | null >( null );

	const status = run?.status ?? null;
	const percent =
		run && run.total > 0
			? Math.round( ( run.processed / run.total ) * 100 )
			: 0;

	const footer = () => {
		if ( status === null ) {
			return (
				<Button
					variant="primary"
					isBusy={ isSubmitting }
					disabled={ file === null || isSubmitting }
					onClick={ () => file && upload( file ) }
				>
					{ __( 'Preview import', 'product-specifications' ) }
				</Button>
			);
		}

		if ( status === 'planned' || status === 'failed' ) {
			return (
				<>
					<Button
						variant="tertiary"
						disabled={ isSubmitting }
						onClick={ discard }
					>
						{ __( 'Discard', 'product-specifications' ) }
					</Button>
					<Button
						variant="primary"
						isBusy={ isSubmitting }
						disabled={ isSubmitting }
						onClick={ start }
					>
						{ status === 'failed'
							? __( 'Resume import', 'product-specifications' )
							: __( 'Start import', 'product-specifications' ) }
					</Button>
				</>
			);
		}

		if ( status === 'complete' && run ) {
			return (
				<>
					<Button variant="secondary" onClick={ downloadReport }>
						{ __( 'Download report', 'product-specifications' ) }
					</Button>
					<Button
						variant="primary"
						disabled={ isSubmitting }
						onClick={ () => {
							setFile( null );
							discard();
						} }
					>
						{ __( 'Done', 'product-specifications' ) }
					</Button>
				</>
			);
		}

		return null;
	};

	return (
		<Card>
			<CardHeader>
				<h2 className="ps-transfer__title">
					{ __( 'Import', 'product-specifications' ) }
				</h2>
				{ run && (
					<span className="ps-transfer__help">{ run.file_name }</span>
				) }
			</CardHeader>
			<CardBody className="ps-transfer__body">
				{ failure && (
					<Notice status="error" onRemove={ dismissFailure }>
						<p>{ failure.message }</p>
						{ failure.details.length > 0 && (
							<ul className="ps-transfer__errors">
								{ failure.details.map( ( detail ) => (
									<li key={ detail }>{ detail }</li>
								) ) }
							</ul>
						) }
					</Notice>
				) }
				{ ! isLoaded && ! failure && <Spinner /> }
				{ isLoaded && status === null && (
					<>
						<p>
							{ __(
								'Bring in a file made by the export. You see what it will change before anything is written.',
								'product-specifications'
							) }
						</p>
						<input
							type="file"
							accept=".json,application/json"
							aria-label={ __(
								'Package file',
								'product-specifications'
							) }
							onChange={ ( event ) =>
								setFile( event.target.files?.[ 0 ] ?? null )
							}
						/>
					</>
				) }
				{ run && status === 'planned' && (
					<>
						<p>
							{ __(
								'Nothing has been changed yet. This is what the import will do:',
								'product-specifications'
							) }
						</p>
						{ run.unknown_sections.length > 0 && (
							<Notice status="warning" isDismissible={ false }>
								{ sprintf(
									/* translators: %s: comma-separated section keys. */
									__(
										'This site does not know these sections, so they are left out: %s',
										'product-specifications'
									),
									run.unknown_sections.join( ', ' )
								) }
							</Notice>
						) }
						<OutcomeSummary run={ run } />
					</>
				) }
				{ run && ( status === 'running' || status === 'failed' ) && (
					<>
						{ status === 'failed' && run.failure && (
							<Notice status="error" isDismissible={ false }>
								{ run.failure }
							</Notice>
						) }
						<ProgressBar
							className="ps-transfer__bar"
							value={ percent }
						/>
						<span className="ps-transfer__help">
							{ sprintf(
								/* translators: 1: entries imported, 2: entries in total. */
								__(
									'%1$s of %2$s entries imported',
									'product-specifications'
								),
								run.processed.toLocaleString(),
								run.total.toLocaleString()
							) }
						</span>
					</>
				) }
				{ run && status === 'complete' && (
					<>
						<Notice status="success" isDismissible={ false }>
							{ __(
								'The import is complete.',
								'product-specifications'
							) }
						</Notice>
						<OutcomeSummary run={ run } />
					</>
				) }
			</CardBody>
			{ isLoaded && footer() !== null && (
				<CardFooter justify="flex-end">{ footer() }</CardFooter>
			) }
		</Card>
	);
};

export default ImportCard;
