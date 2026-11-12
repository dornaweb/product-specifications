import { __, sprintf } from '@wordpress/i18n';

export const SOURCE = 'source';
export const NAME = 'name';
export const ATTRIBUTE = 'attribute';

export interface ProductDataChoice {
	source: string;
	name: string;
	label: string;
	group: string;
}

export interface ProductDataOptions {
	source: string;
	name: string;
}

export function productDataChoices(): ProductDataChoice[] {
	return window.productSpecificationsProductData?.choices ?? [];
}

export function productDataOptions(
	options: Record< string, unknown >
): ProductDataOptions {
	const source = options[ SOURCE ];
	const name = options[ NAME ];

	return {
		source: typeof source === 'string' ? source : '',
		name: typeof name === 'string' ? name.trim() : '',
	};
}

export function choiceFor(
	options: ProductDataOptions,
	choices: ProductDataChoice[] = productDataChoices()
): ProductDataChoice | undefined {
	return choices.find(
		( choice ) =>
			choice.source === options.source && choice.name === options.name
	);
}

// A custom product attribute exists only on the products that carry it, so
// the site offers no choice for it and its name is typed in.
export function isCustomAttribute(
	options: ProductDataOptions,
	choices: ProductDataChoice[] = productDataChoices()
): boolean {
	return options.source === ATTRIBUTE && ! choiceFor( options, choices );
}

export function productDataLabel(
	options: ProductDataOptions,
	choices: ProductDataChoice[] = productDataChoices()
): string | undefined {
	const choice = choiceFor( options, choices );

	if ( choice ) {
		return choice.label;
	}

	if ( options.source === ATTRIBUTE && options.name !== '' ) {
		return sprintf(
			/* translators: %s: the name of a custom product attribute. */
			__( '%s attribute', 'product-specifications' ),
			options.name
		);
	}

	return undefined;
}

export function requiresProductData(
	options: Record< string, unknown >
): string | undefined {
	const current = productDataOptions( options );

	if ( current.source === '' ) {
		return __(
			'Choose which product data to show.',
			'product-specifications'
		);
	}

	if ( current.source === ATTRIBUTE && current.name === '' ) {
		return __(
			'Enter the name of the custom attribute.',
			'product-specifications'
		);
	}

	return undefined;
}
