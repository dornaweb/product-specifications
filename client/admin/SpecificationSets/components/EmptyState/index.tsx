import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

import './styles.scss';

interface Props {
	createUrl: string;
	onCreateClick: ( event: React.MouseEvent< HTMLAnchorElement > ) => void;
}

const EmptyState = ( { createUrl, onCreateClick }: Props ) => (
	<div className="product-specifications-empty-state">
		<div className="product-specifications-empty-state__icon-wrap">
			<svg
				xmlns="http://www.w3.org/2000/svg"
				width="32"
				height="32"
				viewBox="0 0 24 24"
				fill="none"
				stroke="currentColor"
				strokeWidth="2"
				strokeLinecap="round"
				strokeLinejoin="round"
				aria-hidden="true"
				focusable="false"
			>
				<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z" />
				<path d="M12 22V12" />
				<polyline points="3.29 7 12 12 20.71 7" />
				<path d="m7.5 4.27 9 5.15" />
			</svg>
		</div>

		<h3 className="product-specifications-empty-state__title">
			{ __( 'No specification sets yet', 'product-specifications' ) }
		</h3>

		<p className="product-specifications-empty-state__description">
			{ __(
				'Create your first specification set to define structured product specifications such as display, battery, dimensions, warranty, or technical details.',
				'product-specifications'
			) }
		</p>

		<Button
			variant="primary"
			href={ createUrl }
			onClick={ onCreateClick }
			className="product-specifications-empty-state__button"
		>
			{ __( '+ Create Specification Set', 'product-specifications' ) }
		</Button>
	</div>
);

export default EmptyState;
