import React from 'react';
import Split from 'react-split';

export interface SplitViewProps extends React.HTMLAttributes<HTMLDivElement> {
    sizes?: [number, number],
    minSize?: number | [number, number],
    expandToMin?: boolean,
    gutterSize?: number,
    gutterAlign?: 'center' | 'start' | 'end',
    snapOffset?: number,
    dragInterval?: number,
    direction?: 'horizontal' | 'vertical',
    cursor?: string,
    onGutterDrag?: (sizes: [number, number]) => void,
    onGutterDragStart?: (sizes: [number, number]) => void,
    onGutterDragEnd?: (sizes: [number, number]) => void,
}


const SplitView: React.FC<SplitViewProps> = (
    {
        children,
        onGutterDrag,
        onGutterDragStart,
        onGutterDragEnd,
        ...rest
    }
) => (
    // @ts-ignore
    <Split className="split-view" {...rest} onDrag={onGutterDrag} onDragStart={onGutterDragStart} onDragEnd={onGutterDragEnd}>
        {children}
    </Split>
);

SplitView.defaultProps = {
    direction: 'horizontal',
    sizes: [50, 50],
    minSize: 400,
    expandToMin: true,
    gutterSize: 7,
    gutterAlign: 'center',
    snapOffset: 30,
    dragInterval: 1,
};

export default SplitView;
