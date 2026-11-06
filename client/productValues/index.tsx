import { createRoot } from '@wordpress/element';
import ProductValuesPanel from './ProductValuesPanel';
import './styles.scss';

const mount = document.getElementById( 'product-specifications-values-root' );

if ( mount ) {
	const productId = Number( mount.dataset.productId ?? 0 );

	if ( productId > 0 ) {
		createRoot( mount ).render(
			<ProductValuesPanel productId={ productId } />
		);
	}
}
