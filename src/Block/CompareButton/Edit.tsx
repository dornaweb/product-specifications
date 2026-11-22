import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import type { BlockEditProps } from '@wordpress/blocks';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ProductControl from '../ProductSpecifications/ProductControl';

type Attributes = {
	productId?: number;
};

const Edit = ( {
	attributes,
	setAttributes,
}: BlockEditProps< Attributes > ) => (
	<div { ...useBlockProps() }>
		<InspectorControls>
			<PanelBody title={ __( 'Product', 'product-specifications' ) }>
				<ProductControl
					productId={ attributes.productId }
					onChange={ ( productId ) => setAttributes( { productId } ) }
				/>
			</PanelBody>
		</InspectorControls>
		<span className="ps-compare-button wp-element-button">
			{ __( 'Compare', 'product-specifications' ) }
		</span>
	</div>
);

export default Edit;
