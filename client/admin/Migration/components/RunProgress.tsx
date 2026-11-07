import {
	Card,
	CardBody,
	CardFooter,
	CardHeader,
	ProgressBar,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import type { ReactNode } from 'react';
import { stepTitle } from '../labels';
import type { MigrationRun, StepScope } from '../types';

interface Props {
	run: MigrationRun;
	scope: StepScope[];
	action?: ReactNode;
}

const RunProgress = ( { run, scope, action }: Props ) => {
	const position = scope.findIndex( ( step ) => step.key === run.step );
	const percent =
		run.total > 0 ? Math.round( ( run.processed / run.total ) * 100 ) : 0;

	return (
		<Card>
			<CardHeader>
				<h2 className="ps-migration__title">
					{ stepTitle( run.step ) }
				</h2>
				{ position >= 0 && (
					<span className="ps-migration__help">
						{ sprintf(
							/* translators: 1: the current step's number, 2: the number of steps. */
							__( 'Step %1$d of %2$d', 'product-specifications' ),
							position + 1,
							scope.length
						) }
					</span>
				) }
			</CardHeader>
			<CardBody className="ps-migration__progress">
				<ProgressBar className="ps-migration__bar" value={ percent } />
				<span className="ps-migration__help">
					{ sprintf(
						/* translators: 1: items processed, 2: items in total. */
						__(
							'%1$s of %2$s processed',
							'product-specifications'
						),
						run.processed.toLocaleString(),
						run.total.toLocaleString()
					) }
				</span>
			</CardBody>
			{ action && <CardFooter justify="flex-end">{ action }</CardFooter> }
		</Card>
	);
};

export default RunProgress;
