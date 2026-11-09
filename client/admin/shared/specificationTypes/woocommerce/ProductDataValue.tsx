import { Notice } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import type { SpecificationTypeValueProps } from '../index';
import { productDataLabel, productDataOptions } from './productData';

const ProductDataValue = ( { options }: SpecificationTypeValueProps ) => {
	const label = productDataLabel( productDataOptions( options ) );

	if ( label === undefined ) {
		return (
			<Notice status="warning" isDismissible={ false }>
				{ __(
					'The product data this specification reads is no longer available on this site.',
					'product-specifications'
				) }
			</Notice>
		);
	}

	return (
		<p className="ps-product-data-value">
			{ sprintf(
				/* translators: %s: the product data a specification reads, such as Weight or Brands. */
				__( 'Read from the product’s %s.', 'product-specifications' ),
				label
			) }
		</p>
	);
};

export default ProductDataValue;
