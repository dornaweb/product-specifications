import { emptyStructure } from '../../shared/contracts/structure';
import { presentationForNewSet } from '../../shared/contracts/presentation';
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
	presentation: presentationForNewSet(),
};

export const DEFAULT_STATE: SpecificationSetEditorState = {
	postId: null,
	form: DEFAULT_FORM,
	hasUnsavedChanges: false,
	isSaving: false,
	notice: null,
};
