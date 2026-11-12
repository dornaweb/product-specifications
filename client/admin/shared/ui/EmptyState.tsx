import type { ReactNode } from 'react';
import { Icon } from '@wordpress/components';

export interface EmptyStateStep {
	title: string;
	copy: string;
}

interface Props {
	icon?: JSX.Element;
	title: string;
	copy: string;
	actions?: ReactNode;
	steps?: EmptyStateStep[];
}

// One block for every empty state. Steps are given only on a genuine first run,
// where naming the whole journey is what the merchant needs; a filtered list
// with no matches gets the same block without them.
const EmptyState = ( { icon, title, copy, actions, steps }: Props ) => (
	<div className="ps-empty">
		<div className="ps-empty__intro">
			{ icon && (
				<span className="ps-empty__icon">
					<Icon icon={ icon } />
				</span>
			) }
			<h2 className="ps-empty__title">{ title }</h2>
			<p className="ps-empty__copy">{ copy }</p>
			{ actions && <div className="ps-empty__actions">{ actions }</div> }
		</div>
		{ steps && (
			<ol className="ps-empty__steps">
				{ steps.map( ( step, index ) => (
					<li
						key={ step.title }
						className="ps-empty__step"
						aria-current={ index === 0 ? 'step' : undefined }
					>
						<span className="ps-empty__step-number">
							{ index + 1 }
						</span>
						<span>
							<span className="ps-empty__step-title">
								{ step.title }
							</span>
							<span className="ps-empty__step-copy">
								{ step.copy }
							</span>
						</span>
					</li>
				) ) }
			</ol>
		) }
	</div>
);

export default EmptyState;
