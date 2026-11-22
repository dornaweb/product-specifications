import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

const Edit = () => (
	<div { ...useBlockProps() }>
		<Placeholder
			label={ metadata.title }
			instructions={ __(
				'Shoppers see the products they compare here. They arrive from the compare button on a product page.',
				'product-specifications'
			) }
		/>
	</div>
);

export default Edit;
