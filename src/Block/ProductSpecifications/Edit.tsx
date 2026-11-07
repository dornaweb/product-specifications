import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import type { BlockEditProps } from '@wordpress/blocks';
import { PanelBody, Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import ProductControl from './ProductControl';
import metadata from './block.json';

type Attributes = {
	productId?: number;
};

const Edit = ( {
	attributes,
	setAttributes,
}: BlockEditProps< Attributes > ) => {
	const { productId } = attributes;

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Product', 'product-specifications' ) }>
					<ProductControl
						productId={ productId }
						onChange={ ( next ) =>
							setAttributes( { productId: next } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block={ metadata.name }
				attributes={ attributes }
				EmptyResponsePlaceholder={ () => (
					<Placeholder
						label={ metadata.title }
						instructions={
							productId
								? __(
										'This product has no specifications to show.',
										'product-specifications'
								  )
								: __(
										'Shows the specifications of the product being viewed. To preview a product here, choose it in the block settings.',
										'product-specifications'
								  )
						}
					/>
				) }
			/>
		</div>
	);
};

export default Edit;
