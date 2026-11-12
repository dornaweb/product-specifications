import type {
	AddGroupAction,
	AddItemAction,
	DismissNoticeAction,
	FailSaveAction,
	FinishSaveAction,
	GeneralField,
	GroupProperty,
	InitializeEditorAction,
	ItemProperty,
	MoveGroupAction,
	MoveItemAction,
	MoveItemToGroupAction,
	NoticeState,
	RemoveGroupAction,
	RemoveItemAction,
	SpecificationSetGeneralState,
	SpecificationSetStructure,
	StartSavingAction,
	StructureGroup,
	StructureItem,
	UpdateGeneralFieldAction,
	UpdateGroupAction,
	UpdateItemAction,
} from './types';

export const actions = {
	initializeEditor(
		postId: number | null,
		general: SpecificationSetGeneralState,
		structure: SpecificationSetStructure
	): InitializeEditorAction {
		return { type: 'INITIALIZE_EDITOR', postId, general, structure };
	},
	updateGeneralField(
		field: GeneralField,
		value: SpecificationSetGeneralState[ GeneralField ]
	): UpdateGeneralFieldAction {
		return { type: 'UPDATE_GENERAL_FIELD', field, value };
	},
	addGroup(
		group: StructureGroup,
		position: number | null = null
	): AddGroupAction {
		return { type: 'ADD_GROUP', group, position };
	},
	addItem(
		groupKey: string,
		item: StructureItem,
		position: number | null = null
	): AddItemAction {
		return { type: 'ADD_ITEM', groupKey, item, position };
	},
	updateGroup(
		groupKey: string,
		property: GroupProperty,
		value: StructureGroup[ GroupProperty ]
	): UpdateGroupAction {
		return { type: 'UPDATE_GROUP', groupKey, property, value };
	},
	moveGroup( groupKey: string, offset: number ): MoveGroupAction {
		return { type: 'MOVE_GROUP', groupKey, offset };
	},
	removeGroup(
		groupKey: string,
		keepItemsIn: string | null = null
	): RemoveGroupAction {
		return { type: 'REMOVE_GROUP', groupKey, keepItemsIn };
	},
	updateItem(
		groupKey: string,
		slug: string,
		property: ItemProperty,
		value: boolean
	): UpdateItemAction {
		return { type: 'UPDATE_ITEM', groupKey, slug, property, value };
	},
	moveItem( groupKey: string, slug: string, offset: number ): MoveItemAction {
		return { type: 'MOVE_ITEM', groupKey, slug, offset };
	},
	moveItemToGroup(
		fromGroupKey: string,
		toGroupKey: string,
		slug: string,
		position: number | null = null
	): MoveItemToGroupAction {
		return {
			type: 'MOVE_ITEM_TO_GROUP',
			fromGroupKey,
			toGroupKey,
			slug,
			position,
		};
	},
	removeItem( groupKey: string, slug: string ): RemoveItemAction {
		return { type: 'REMOVE_ITEM', groupKey, slug };
	},
	startSaving(): StartSavingAction {
		return { type: 'START_SAVING' };
	},
	finishSave(
		postId: number,
		general: SpecificationSetGeneralState,
		structure: SpecificationSetStructure,
		notice: NoticeState
	): FinishSaveAction {
		return { type: 'FINISH_SAVE', postId, general, structure, notice };
	},
	failSave( notice: NoticeState ): FailSaveAction {
		return { type: 'FAIL_SAVE', notice };
	},
	dismissNotice(): DismissNoticeAction {
		return { type: 'DISMISS_NOTICE' };
	},
};
