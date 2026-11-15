import { Card, CardBody, CardHeader } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import type { ReactNode } from 'react';
import { itemLabel } from '../labels';
import type { MigrationRun, SkippedEntry } from '../types';

const SHOWN = 50;

// Skips and errors are listed apart. A skip is an item that already held data
// in the new format, which is the expected outcome for one edited early, and
// not a failure.
const RunLog = ( { run }: { run: MigrationRun } ) => (
	<>
		{ run.errors.length > 0 && (
			<LogCard
				title={ __( 'Not migrated', 'product-specifications' ) }
				entries={ run.errors }
				render={ ( entry ) => (
					<>
						<b>{ itemLabel( entry.step, entry.item ) }</b>{ ' ' }
						{ entry.message }
					</>
				) }
			/>
		) }
		{ run.skipped.length > 0 && (
			<LogCard
				title={ __( 'Left as they are', 'product-specifications' ) }
				description={ __(
					'These already held data in the new format, so the migration did not change them.',
					'product-specifications'
				) }
				entries={ run.skipped }
				render={ ( entry ) => itemLabel( entry.step, entry.item ) }
			/>
		) }
	</>
);

interface LogCardProps< Entry extends SkippedEntry > {
	title: string;
	description?: string;
	entries: Entry[];
	render: ( entry: Entry ) => ReactNode;
}

function LogCard< Entry extends SkippedEntry >( {
	title,
	description,
	entries,
	render,
}: LogCardProps< Entry > ) {
	return (
		<Card>
			<CardHeader>
				<h2 className="ps-migration__title">{ title }</h2>
				<span className="ps-migration__help">
					{ entries.length.toLocaleString() }
				</span>
			</CardHeader>
			<CardBody className="ps-migration__log">
				{ description && <p>{ description }</p> }
				<ul>
					{ entries.slice( 0, SHOWN ).map( ( entry ) => (
						<li key={ `${ entry.step }-${ entry.item }` }>
							{ render( entry ) }
						</li>
					) ) }
				</ul>
				{ entries.length > SHOWN && (
					<p className="ps-migration__help">
						{ sprintf(
							/* translators: %d: how many more entries are not listed. */
							_n(
								'And %d more.',
								'And %d more.',
								entries.length - SHOWN,
								'product-specifications'
							),
							entries.length - SHOWN
						) }
					</p>
				) }
			</CardBody>
		</Card>
	);
}

export default RunLog;
