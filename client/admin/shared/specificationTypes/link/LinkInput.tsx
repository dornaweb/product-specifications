import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { SpecificationTypeValueProps } from '../index';
import { enteredLink, storedLink } from './linkValue';
import './styles.scss';

const LinkInput = ( {
	label,
	value,
	onChange,
}: SpecificationTypeValueProps ) => {
	const current = enteredLink( value );

	return (
		<div className="ps-link-input">
			<TextControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ label }
				hideLabelFromVision
				inputMode="url"
				placeholder={ __(
					'https://…, mailto:… or tel:…',
					'product-specifications'
				) }
				value={ current.url }
				onChange={ ( url ) =>
					onChange( storedLink( { ...current, url } ) )
				}
			/>
			<TextControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'Link label', 'product-specifications' ) }
				hideLabelFromVision
				placeholder={ __(
					'Label (optional)',
					'product-specifications'
				) }
				value={ current.label }
				onChange={ ( next ) =>
					onChange( storedLink( { ...current, label: next } ) )
				}
			/>
		</div>
	);
};

export default LinkInput;
