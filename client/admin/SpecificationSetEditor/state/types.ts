import type { NoticeState } from '../../shared/ui/Notice';
import type {
	SpecificationSetStructure,
	StructureGroup,
	StructureItem,
} from '../mapping/specificationSetStructure';

export type { NoticeState };

export type SpecificationSetStatus = 'publish' | 'draft';

export interface SpecificationSetGeneralState {
	title: string;
	slug: string;
	description: string;
	status: SpecificationSetStatus;
}

export interface SpecificationSetEditorFormState {
	general: SpecificationSetGeneralState;
	structure: SpecificationSetStructure;
}

export type GroupProperty = 'label' | 'description' | 'visible';
export type ItemProperty = 'visible';

export interface SpecificationSetEditorState {
	postId: number | null;
	form: SpecificationSetEditorFormState;
	hasUnsavedChanges: boolean;
	isSaving: boolean;
	notice: NoticeState | null;
}

export type GeneralField = keyof SpecificationSetGeneralState;

export interface UpdateGeneralFieldAction {
	type: 'UPDATE_GENERAL_FIELD';
	field: GeneralField;
	value: SpecificationSetGeneralState[ GeneralField ];
}

export interface InitializeEditorAction {
	type: 'INITIALIZE_EDITOR';
	postId: number | null;
	general: SpecificationSetGeneralState;
	structure: SpecificationSetStructure;
}

export interface AddGroupAction {
	type: 'ADD_GROUP';
	group: StructureGroup;
	position: number | null;
}

export interface AddItemAction {
	type: 'ADD_ITEM';
	groupKey: string;
	item: StructureItem;
	position: number | null;
}

export interface UpdateGroupAction {
	type: 'UPDATE_GROUP';
	groupKey: string;
	property: GroupProperty;
	value: StructureGroup[ GroupProperty ];
}

export interface MoveGroupAction {
	type: 'MOVE_GROUP';
	groupKey: string;
	offset: number;
}

export interface RemoveGroupAction {
	type: 'REMOVE_GROUP';
	groupKey: string;
	keepItemsIn: string | null;
}

export interface UpdateItemAction {
	type: 'UPDATE_ITEM';
	groupKey: string;
	slug: string;
	property: ItemProperty;
	value: boolean;
}

export interface MoveItemAction {
	type: 'MOVE_ITEM';
	groupKey: string;
	slug: string;
	offset: number;
}

export interface MoveItemToGroupAction {
	type: 'MOVE_ITEM_TO_GROUP';
	fromGroupKey: string;
	toGroupKey: string;
	slug: string;
	position: number | null;
}

export interface RemoveItemAction {
	type: 'REMOVE_ITEM';
	groupKey: string;
	slug: string;
}

export interface StartSavingAction {
	type: 'START_SAVING';
}

export interface FinishSaveAction {
	type: 'FINISH_SAVE';
	postId: number;
	general: SpecificationSetGeneralState;
	structure: SpecificationSetStructure;
	notice: NoticeState;
}

export interface FailSaveAction {
	type: 'FAIL_SAVE';
	notice: NoticeState;
}

export interface DismissNoticeAction {
	type: 'DISMISS_NOTICE';
}

export type SpecificationSetEditorAction =
	| InitializeEditorAction
	| UpdateGeneralFieldAction
	| AddGroupAction
	| AddItemAction
	| UpdateGroupAction
	| MoveGroupAction
	| RemoveGroupAction
	| UpdateItemAction
	| MoveItemAction
	| MoveItemToGroupAction
	| RemoveItemAction
	| StartSavingAction
	| FinishSaveAction
	| FailSaveAction
	| DismissNoticeAction;

export type { SpecificationSetStructure, StructureGroup, StructureItem };
