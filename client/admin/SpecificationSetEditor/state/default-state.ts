import { emptyStructure } from '../mapping/specificationSetStructure';
import type {
	SpecificationSetEditorFormState,
	SpecificationSetEditorState,
} from './types';

const DEFAULT_FORM: SpecificationSetEditorFormState = {
	general: {
		title: '',
		slug: '',
		description: '',
		status: 'draft',
	},
	structure: emptyStructure(),
};

export const DEFAULT_STATE: SpecificationSetEditorState = {
	postId: null,
	form: DEFAULT_FORM,
	hasUnsavedChanges: false,
	isSaving: false,
	notice: null,
};
