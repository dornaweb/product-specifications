import { __, sprintf } from '@wordpress/i18n';
import { OUTCOME_ORDER, outcomeTitle, sectionTitle } from '../import/labels';
import type { ImportRun } from '../import/types';

interface Props {
	run: ImportRun;
}

const OutcomeSummary = ( { run }: Props ) => {
	const sections = Object.keys( run.counts );

	if ( sections.length === 0 ) {
		return (
			<p>
				{ __(
					'The file holds nothing to import.',
					'product-specifications'
				) }
			</p>
		);
	}

	return (
		<>
			<div className="ps-transfer__table-wrap">
				<table className="ps-transfer__table">
					<thead>
						<tr>
							<th scope="col">
								{ __( 'Section', 'product-specifications' ) }
							</th>
							{ OUTCOME_ORDER.map( ( status ) => (
								<th scope="col" key={ status }>
									{ outcomeTitle( status ) }
								</th>
							) ) }
						</tr>
					</thead>
					<tbody>
						{ sections.map( ( section ) => (
							<tr key={ section }>
								<th scope="row">{ sectionTitle( section ) }</th>
								{ OUTCOME_ORDER.map( ( status ) => (
									<td key={ status }>
										{ (
											run.counts[ section ][ status ] ?? 0
										).toLocaleString() }
									</td>
								) ) }
							</tr>
						) ) }
					</tbody>
				</table>
			</div>
			{ run.notable.length > 0 && (
				<details className="ps-transfer__notable">
					<summary>
						{ sprintf(
							/* translators: %s: number of entries. */
							__(
								'Entries to look at (%s)',
								'product-specifications'
							),
							run.notable_total.toLocaleString()
						) }
					</summary>
					<ul>
						{ run.notable.map( ( entry, index ) => (
							<li key={ index }>
								<strong>
									{ sectionTitle( entry.section ) } ·{ ' ' }
									{ entry.label }
								</strong>{ ' ' }
								<span className="ps-transfer__help">
									{ outcomeTitle( entry.status ) }
								</span>
								{ entry.notes.length > 0 && (
									<ul>
										{ entry.notes.map( ( note ) => (
											<li key={ note }>{ note }</li>
										) ) }
									</ul>
								) }
							</li>
						) ) }
					</ul>
					{ run.notable_total > run.notable.length && (
						<p className="ps-transfer__help">
							{ sprintf(
								/* translators: 1: entries listed, 2: entries in total. */
								__(
									'Showing the first %1$s of %2$s.',
									'product-specifications'
								),
								run.notable.length.toLocaleString(),
								run.notable_total.toLocaleString()
							) }
						</p>
					) }
				</details>
			) }
		</>
	);
};

export default OutcomeSummary;
