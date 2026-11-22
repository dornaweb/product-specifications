import type {
	SpecificationSetEditorFormState,
	NoticeState,
	SpecificationSetEditorState,
	SpecificationSetGeneralState,
	SpecificationSetPresentation,
	SpecificationSetStructure,
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
	getStructure(
		state: SpecificationSetEditorState
	): SpecificationSetStructure {
		return state.form.structure;
	},
	getPresentation(
		state: SpecificationSetEditorState
	): SpecificationSetPresentation {
		return state.form.presentation;
	},
	hasUnsavedChanges( state: SpecificationSetEditorState ): boolean {
		return state.hasUnsavedChanges;
	},
	isSaving( state: SpecificationSetEditorState ): boolean {
		return state.isSaving;
	},
	getNotice( state: SpecificationSetEditorState ): NoticeState | null {
		return state.notice;
	},
};
