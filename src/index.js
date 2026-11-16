/**
 * Nirmaan Builder editor entry point.
 *
 * The full canvas/editor will be implemented incrementally. Keep the editor
 * isolated from WordPress admin screens and communicate through REST APIs.
 */
import './style.scss';

export function getEditorBootstrap() {
	return {
		name: 'Nirmaan Builder',
		version: '0.1.0',
		status: 'foundation',
	};
}
