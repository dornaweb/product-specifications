import { __ } from '@wordpress/i18n';
import type { EditorPlaceholderTabName } from './types';

const PLACEHOLDER_COPY: Record< EditorPlaceholderTabName, string > = {
	groups: __(
		'Groups and specifications will be managed here.',
		'product-specifications'
	),
	assignments: __(
		'Product assignments will be configured here.',
		'product-specifications'
	),
	comparison: __(
		'Comparison settings will be configured here.',
		'product-specifications'
	),
	preview: __(
		'The specification table preview will be shown here.',
		'product-specifications'
	),
	advanced: __(
		'Advanced settings will be configured here.',
		'product-specifications'
	),
};

const PlaceholderTab = ( { tab }: { tab: EditorPlaceholderTabName } ) => (
	<div className="product-specifications-specs-set-editor__placeholder">
		{ PLACEHOLDER_COPY[ tab ] }
	</div>
);

export default PlaceholderTab;
