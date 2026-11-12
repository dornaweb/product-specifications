export type EditorTabName = 'general' | 'specifications';

export interface EditorTab {
	name: EditorTabName;
	title: string;
	className?: string;
}
