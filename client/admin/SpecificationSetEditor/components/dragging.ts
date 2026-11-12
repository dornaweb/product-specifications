import type { DragEvent } from 'react';

export type DragPayload =
	| {
			kind: 'specification';
			slug: string;
			termId: number;
			fromGroupKey: string | null;
	  }
	| { kind: 'group'; groupKey: string };

// Firefox starts no drag unless data is set, but a drop reads its payload from
// the composer's own state: `dragover` is not allowed to look inside the
// transfer, so the two would have to agree twice.
export function beginDrag( event: DragEvent, label: string ): void {
	event.dataTransfer.setData( 'text/plain', label );
	event.dataTransfer.effectAllowed = 'move';
}

export function acceptDrop( event: DragEvent ): void {
	event.preventDefault();
	event.dataTransfer.dropEffect = 'move';
}
