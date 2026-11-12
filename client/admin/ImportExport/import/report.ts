import { fetchReport } from './api';

export async function downloadReport() {
	const report = await fetchReport();
	const url = window.URL.createObjectURL(
		new window.Blob( [ JSON.stringify( report, null, 2 ) ], {
			type: 'application/json',
		} )
	);
	const link = window.document.createElement( 'a' );

	link.href = url;
	link.download = `${ report.file.replace( /\.json$/i, '' ) }-report.json`;
	link.click();
	window.URL.revokeObjectURL( url );
}
