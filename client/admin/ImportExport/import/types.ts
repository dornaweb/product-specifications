export type ImportStatus = 'planned' | 'running' | 'failed' | 'complete';

export type OutcomeStatus =
	| 'create'
	| 'update'
	| 'unchanged'
	| 'conflict'
	| 'skip'
	| 'failed';

export interface Outcome {
	status: OutcomeStatus;
	label: string;
	notes: string[];
}

export interface NotableOutcome extends Outcome {
	section: string;
}

export interface ImportRun {
	status: ImportStatus;
	file_name: string;
	processed: number;
	total: number;
	failure: string | null;
	unknown_sections: string[];
	counts: Record< string, Partial< Record< OutcomeStatus, number > > >;
	notable: NotableOutcome[];
	notable_total: number;
}

export interface ImportReport {
	file: string;
	counts: ImportRun[ 'counts' ];
	sections: Record< string, Record< string, Outcome > >;
}

export interface ImportPayload {
	run: ImportRun | null;
}
