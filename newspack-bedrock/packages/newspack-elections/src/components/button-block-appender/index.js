/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * Internal dependencies
 */

import { useBlockEditContext, ButtonBlockAppender as DefaultButtonBlockAppender} from '@wordpress/block-editor';

export function ButtonBlockAppender( {
	showSeparator,
	isFloating,
	onAddBlock,
	isToggle,
	className = "gp-block-editor-block-appender"
} ) {
	const { clientId } = useBlockEditContext();
	
	
	return (
		<div className = {className}>
			<DefaultButtonBlockAppender
				className={ clsx( {
					'block-list-appender__toggle': isToggle,
				} )  }
				rootClientId={ clientId }
				showSeparator={ showSeparator }
				isFloating={ isFloating }
				onAddBlock={ onAddBlock }
			/>
		</div>
	);
}