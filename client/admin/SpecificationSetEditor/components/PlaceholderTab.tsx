import { __ } from '@wordpress/i18n';
import type { EditorPlaceholderTabName } from './types';

const PLACEHOLDER_COPY: Record< EditorPlaceholderTabName, string > = {
	comparison: __(
		'Comparison settings will be configured here.',
		'product-specifications'
	),
	layout: __(
		'Layout settings will be configured here.',
		'product-specifications'
	),
};

const PlaceholderTab = ( { tab }: { tab: EditorPlaceholderTabName } ) => (
	<div className="product-specifications-specs-set-editor__placeholder">
		{ PLACEHOLDER_COPY[ tab ] }
	</div>
);

export default PlaceholderTab;
