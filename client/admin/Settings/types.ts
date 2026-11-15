export type SettingValue = string | boolean | number | string[];

export type SettingFieldType =
	| 'text'
	| 'textarea'
	| 'number'
	| 'toggle'
	| 'select'
	| 'radio'
	| 'checkbox_group'
	| 'color'
	| 'media';

export interface SettingOption {
	value: string;
	label: string;
}

export interface SettingFieldDefinition {
	key: string;
	type: SettingFieldType;
	section: string;
	label: string;
	description: string;
	controlLabel?: string;
	default: SettingValue;
	options: SettingOption[];
	developerOnly: boolean;
	destructive: boolean;
}

export interface SettingSectionDefinition {
	key: string;
	title: string;
	description: string;
}

export interface SettingsResponse {
	settings: Record< string, SettingValue >;
	errors: Record< string, string >;
	fields: SettingFieldDefinition[];
	sections: SettingSectionDefinition[];
	canManage: boolean;
}

export interface SettingsRequestFailure {
	message: string;
	fields: Record< string, string >;
}
