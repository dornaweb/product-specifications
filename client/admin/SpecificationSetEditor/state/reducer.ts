import { DEFAULT_STATE } from './default-state';
import type {
	SpecificationSetEditorAction,
	SpecificationSetEditorState,
} from './types';

export const reducer = (
	state = DEFAULT_STATE,
	action: SpecificationSetEditorAction
): SpecificationSetEditorState => {
	switch ( action.type ) {
		case 'INITIALIZE_EDITOR':
			return {
				...state,
				postId: action.postId,
				hasUnsavedChanges: false,
				isSaving: false,
				notice: null,
				form: {
					...state.form,
					general: action.general,
				},
			};
		case 'UPDATE_GENERAL_FIELD':
			return {
				...state,
				hasUnsavedChanges: true,
				notice: null,
				form: {
					...state.form,
					general: {
						...state.form.general,
						[ action.field ]: action.value,
					},
				},
			};
		case 'START_SAVING':
			return {
				...state,
				isSaving: true,
				notice: null,
			};
		case 'FINISH_SAVE':
			return {
				...state,
				postId: action.postId,
				hasUnsavedChanges: false,
				isSaving: false,
				notice: action.notice,
				form: {
					...state.form,
					general: action.general,
				},
			};
		case 'FAIL_SAVE':
			return {
				...state,
				isSaving: false,
				notice: action.notice,
			};
		case 'DISMISS_NOTICE':
			return {
				...state,
				notice: null,
			};
		default:
			return state;
	}
};
