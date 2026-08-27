import type {
	SpecificationSetEditorFormState,
	SpecificationSetEditorNotice,
	SpecificationSetEditorState,
	SpecificationSetGeneralState,
} from './types';

export const selectors = {
	getPostId( state: SpecificationSetEditorState ): number | null {
		return state.postId;
	},
	getForm(
		state: SpecificationSetEditorState
	): SpecificationSetEditorFormState {
		return state.form;
	},
	getGeneral(
		state: SpecificationSetEditorState
	): SpecificationSetGeneralState {
		return state.form.general;
	},
	hasUnsavedChanges( state: SpecificationSetEditorState ): boolean {
		return state.hasUnsavedChanges;
	},
	isSaving( state: SpecificationSetEditorState ): boolean {
		return state.isSaving;
	},
	getNotice(
		state: SpecificationSetEditorState
	): SpecificationSetEditorNotice | null {
		return state.notice;
	},
};
