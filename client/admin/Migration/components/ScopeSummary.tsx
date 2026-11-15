import { scopeLabel } from '../labels';
import type { StepScope } from '../types';

const ScopeSummary = ( { scope }: { scope: StepScope[] } ) => (
	<ul className="ps-migration__scope">
		{ scope
			.filter( ( step ) => step.total > 0 )
			.map( ( step ) => (
				<li key={ step.key } className="ps-migration__stat">
					<span className="ps-migration__count">
						{ step.total.toLocaleString() }
					</span>
					<span>{ scopeLabel( step.key, step.total ) }</span>
				</li>
			) ) }
	</ul>
);

export default ScopeSummary;
