import { __ } from '@wordpress/i18n';
import ChoiceOptionsPanel, {
	parseChoices,
	choicesText,
} from './ChoiceOptionsPanel';
import {
	ChoiceDefault,
	ParagraphDefault,
	TextDefault,
	TrueFalseDefault,
	UnitDefault,
} from './defaultControls';
import type { SpecificationTypes } from './index';
import YesNoOptionsPanel from './YesNoOptionsPanel';
import MeasurementInput from './unit/MeasurementInput';
import UnitOptionsPanel from './unit/UnitOptionsPanel';
import { unitOptions } from './unit/unitOptions';
import {
	ChoiceValue,
	ParagraphValue,
	TextValue,
	TrueFalseValue,
} from './valueControls';

const requiresChoices = ( options: Record< string, unknown > ) =>
	parseChoices( choicesText( options ) ).length === 0
		? __(
				'Add at least one option, one per line.',
				'product-specifications'
		  )
		: undefined;

const requiresUnits = ( options: Record< string, unknown > ) =>
	unitOptions( options ).units.length === 0
		? __( 'Choose at least one unit.', 'product-specifications' )
		: undefined;

export const coreSpecificationTypes: SpecificationTypes = {
	text: {
		label: __( 'Text', 'product-specifications' ),
		description: __(
			'A short free-form answer.',
			'product-specifications'
		),
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
		label: __( 'Number with unit', 'product-specifications' ),
		description: __(
			'A number, or a range, with a unit such as mm, kg or GB.',
			'product-specifications'
		),
		options: UnitOptionsPanel,
		validateOptions: requiresUnits,
		defaultControl: UnitDefault,
		valueControl: MeasurementInput,
	},
};
