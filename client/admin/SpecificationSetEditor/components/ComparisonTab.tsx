import { __ } from '@wordpress/i18n';
import { ToggleControl } from '@wordpress/components';

interface ComparisonTabProps {
	comparison: boolean;
	updateComparison: ( comparison: boolean ) => void;
}

const ComparisonTab = ( {
	comparison,
	updateComparison,
}: ComparisonTabProps ) => (
	<div className="product-specifications-specs-set-editor__comparison">
		<ToggleControl
			__nextHasNoMarginBottom
			label={ __( 'Enable comparison', 'product-specifications' ) }
			help={ __(
				"Shows the compare button on this set's products and lets shoppers compare them with each other.",
				'product-specifications'
			) }
			checked={ comparison }
			onChange={ updateComparison }
		/>
	</div>
);

export default ComparisonTab;
