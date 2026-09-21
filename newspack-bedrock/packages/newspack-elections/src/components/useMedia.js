import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

const useMedia = ( mediaId ) => {
	const result = useSelect( ( select ) => {
		const selectorArgs = [ mediaId ];

		return {
			media: select( coreStore ).getMedia( ...selectorArgs ),

			isResolving: select( coreStore ).isResolving(
				'getMedia',
				selectorArgs
			),
			hasStartedResolution: select( coreStore ).hasStartedResolution(
				'getMedia', // _selectorName_
				selectorArgs
			),
			hasFinishedResolution: select( coreStore ).hasFinishedResolution(
				'getMedia', selectorArgs
			),
			hasResolutionFailed: select( coreStore ).hasResolutionFailed(
				'getMedia', selectorArgs
			),
		};
	}, [ mediaId ] );

	return result;
};

export { useMedia };