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
};

export const DEFAULT_STATE: SpecificationSetEditorState = {
	postId: null,
	form: DEFAULT_FORM,
	hasUnsavedChanges: false,
	isSaving: false,
	notice: null,
};
