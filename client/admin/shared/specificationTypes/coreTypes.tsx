import { __ } from '@wordpress/i18n';
import ChoiceOptionsPanel, {
	parseChoices,
	choicesText,
} from './ChoiceOptionsPanel';
import {
	ChoiceDefault,
	ParagraphDefault,
	TrueFalseDefault,
	NumberDefault,
	DateDefault,
} from './defaultControls';
import type { SpecificationTypes } from './index';
import DateInput from './date/DateInput';
import DateOptionsPanel from './date/DateOptionsPanel';
import YesNoOptionsPanel from './YesNoOptionsPanel';
import DocumentValue from './document/DocumentValue';
import LinkInput from './link/LinkInput';
import NumberInput from './number/NumberInput';
import NumberOptionsPanel from './number/NumberOptionsPanel';
import { requiresUnits } from './number/numberOptions';
import ProductDataOptionsPanel from './woocommerce/ProductDataOptionsPanel';
import ProductDataValue from './woocommerce/ProductDataValue';
import { requiresProductData } from './woocommerce/productData';
import TextOptionsPanel from './text/TextOptionsPanel';
import { TextDefault, TextValue } from './text/textControls';
import { requiresSuggestions } from './text/textOptions';
import { ChoiceValue, ParagraphValue, TrueFalseValue } from './valueControls';

const requiresChoices = ( options: Record< string, unknown > ) =>
	parseChoices( choicesText( options ) ).length === 0
		? __(
				'Add at least one option, one per line.',
				'product-specifications'
		  )
		: undefined;

export const coreSpecificationTypes: SpecificationTypes = {
	text: {
		label: __( 'Text', 'product-specifications' ),
		description: __(
			'A short free-form answer, optionally with suggestions or several values.',
			'product-specifications'
		),
		options: TextOptionsPanel,
		validateOptions: requiresSuggestions,
		defaultControl: TextDefault,
		valueControl: TextValue,
	},
	textarea: {
		label: __( 'Paragraph', 'product-specifications' ),
		description: __(
			'Several lines of free-form text.',
			'product-specifications'
		),
		defaultControl: ParagraphDefault,
		valueControl: ParagraphValue,
	},
	select: {
		label: __( 'Single select', 'product-specifications' ),
		description: __(
			'One answer from a fixed list you define.',
			'product-specifications'
		),
		options: ChoiceOptionsPanel,
		validateOptions: requiresChoices,
		defaultControl: ChoiceDefault,
		valueControl: ChoiceValue,
	},
	true_false: {
		label: __( 'Yes or no', 'product-specifications' ),
		description: __( 'A single yes or no.', 'product-specifications' ),
		options: YesNoOptionsPanel,
		defaultControl: TrueFalseDefault,
		valueControl: TrueFalseValue,
	},
	unit: {
		label: __( 'Number', 'product-specifications' ),
		description: __(
			'A number or a range, with an optional unit such as mm, kg or GB.',
			'product-specifications'
		),
		options: NumberOptionsPanel,
		validateOptions: requiresUnits,
		defaultControl: NumberDefault,
		valueControl: NumberInput,
	},
	woocommerce: {
		label: __( 'WooCommerce product data', 'product-specifications' ),
		description: __(
			'Read from the product, such as its weight, SKU or brand. Nothing is entered by hand.',
			'product-specifications'
		),
		options: ProductDataOptionsPanel,
		validateOptions: requiresProductData,
		valueControl: ProductDataValue,
		readOnly: true,
	},
	document: {
		label: __( 'Document', 'product-specifications' ),
		description: __(
			'Files from the media library, such as datasheets, manuals or certificates.',
			'product-specifications'
		),
		valueControl: DocumentValue,
	},
	link: {
		label: __( 'Link', 'product-specifications' ),
		description: __(
			'A web address, email address or phone number, with an optional label.',
			'product-specifications'
		),
		valueControl: LinkInput,
	},
	date: {
		label: __( 'Date', 'product-specifications' ),
		description: __(
			'A day, a month or a year, such as a release date.',
			'product-specifications'
		),
		options: DateOptionsPanel,
		defaultControl: DateDefault,
		valueControl: DateInput,
	},
};
